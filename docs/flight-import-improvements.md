# Flight Import: Review Layer + On-Demand Airport Auto-Add

> Implementation plan. Intended for a separate branch, after current changes are merged.

## Context

Flight (booking) import today is a one-shot fire-and-forget:

- Admin uploads an xlsx/csv on a static form (`resources/views/event/admin/import.blade.php`) → POST → `BookingImportController@store` → `(new BookingsImport($event))->import($file)`.
- `BookingsImport` uses `maatwebsite/excel` v3.1 with `WithValidation`. **Any bad row throws an uncaught `Maatwebsite\Excel\Validators\ValidationException` → 500 page.** Not UX-friendly.
- Airport lookup is `$this->airports[$icao]` (pre-loaded `Airport::pluck('id','icao')`). A **missing airport fails validation** (`exists:airports,icao`) and aborts the whole import.

Two improvements, **each in its own commit**:

1. **On-demand airport auto-add** (GitHub issue #400) — stop relying on the bulk airport dataset; when an import references an ICAO not in the DB, fetch it from the `airportsdata` CSV and create it.
2. **Review layer** — after upload, show all parsed rows with per-row validation, let admin select/deselect (incl. select all/none), then import only the selected valid rows. Replaces the throw-on-error flow.

Decisions:
- Airport data source: `https://raw.githubusercontent.com/mborsetti/airportsdata/main/airportsdata/airports.csv` (cols `icao,iata,name,lat,lon,...`), same source `app/Jobs/ImportAirportsJob.php` already uses. **Fetch on-demand, cache the CSV (~24h)**, create only the missing airports.
- Errored rows: **excluded, checkbox disabled** — only valid rows selectable.
- Approach: **Livewire wizard** (upload → review → import), matching project convention.
- Standalone "script validator": **dropped** — review layer's per-row validation covers it.

Commit order: **airports first** (the resolver is a dependency the review layer reuses), then review layer.

---

## Commit 1 — On-demand airport auto-add (issue #400)

### New: `app/Services/AirportImporter.php`
On-demand resolver. Public API:

```php
/**
 * Ensure the given ICAO codes exist as Airports, creating any missing ones
 * from the airportsdata CSV.
 *
 * @param  iterable<string>  $icaoCodes
 * @return array{created: list<string>, unresolved: list<string>}  unresolved = not found in dataset
 */
public function ensure(iterable $icaoCodes): array
```

Logic:
1. Normalise/uppercase + unique the input ICAOs.
2. `$existing = Airport::whereIn('icao', $icaos)->pluck('icao')`.
3. `$missing = $icaos->diff($existing)`. If empty → return early (no network).
4. Fetch CSV body via `Cache::remember('airportsdata.csv', now()->addDay(), fn () => file_get_contents(self::SOURCE_URL))`. Put the URL in a constant/config (reuse the same literal as `ImportAirportsJob`; consider extracting to `config/services.php` or a shared const so both reference one place).
5. Parse CSV into an `icao => [iata,name,lat,lon]` map (only needed once, only when `$missing` non-empty).
6. For each missing ICAO present in the map → `Airport::create([...])` (reuse the field mapping from `app/Imports/AirportsImport.php:30-36`). Collect created.
7. ICAOs not in the map → `unresolved` (truly invalid codes — caller decides; in the old flow these still fail, which is correct).

**`iata` is `NOT NULL UNIQUE`** (`2018_06_06_172719_create_airports_table.php:17`). The dataset has rows with empty `iata`. Handling: if a dataset row's `iata` is blank, treat that ICAO as **unresolved** (skip create) to avoid unique-collision on empty string. Note in code comment. (Real flight airports in the dataset carry an iata; this only affects obscure fields. A future migration could make `iata` nullable if needed — out of scope.)

### Wire into existing controller flow
`app/Http/Controllers/Booking/BookingImportController.php@store`: before constructing `BookingsImport`, read the file's ICAO columns once and call `AirportImporter::ensure()` so previously-missing airports exist before the import's `exists:airports,icao` validation runs.

- Use a lightweight read: `Maatwebsite\Excel\Facades\Excel::toArray()` with a heading-row reader (or `HeadingRowImport`) to collect the relevant columns (`origin,destination` or `airport_1..3` per `EventType::MULTIFLIGHTS`).
- This pre-pass is intentionally minimal; Commit 2 supersedes this controller path but Commit 1 must stand alone and be independently shippable.

> Keep `ImportAirportsJob`/`AirportsImport` as-is (still useful for seeding). Removing the scheduled bulk load is out of scope for this plan.

### Tests
- `tests/Feature/Services/AirportImporterTest.php` (Pest): `Http::fake()` / fake the CSV body; assert missing ICAOs get created, existing untouched, unknown ICAOs returned as `unresolved`, blank-iata rows treated as unresolved. Use `AirportFactory` for pre-existing airports.
- Extend `tests/Feature/Http/Booking/BookingImportControllerTest.php`: upload a booking file referencing an ICAO not yet in DB; assert the airport is auto-created and the booking imports (fake the CSV fetch).

---

## Commit 2 — Review layer (Livewire wizard)

### New: `app/Livewire/Booking/Admin/Import.php`
Full-page-style component embedded in a controller view (project convention: `<livewire:airport.admin.overview />` in `resources/views/airport/admin/overview.blade.php:21`).

Traits/attrs: `WithFileUploads`. Authz in `render()`/`mount()`: `abort_unless(auth()->user()?->isAdmin, 404)` (matches `app/Livewire/Airport/Admin/Overview.php:26`). `mount(Event $event)`.

State:
- `public Event $event;`
- `public $file;` (uploaded temp file)
- `public array $rows = [];` — each: `['data' => [...], 'errors' => [...], 'valid' => bool]`
- `public array $selected = [];` — indices of checked rows
- (optional) `public array $autoAddedAirports = [];` for a "created N airports" notice

Flow:
1. **Upload** — `updatedFile()` (or an explicit `parse()` action): read rows with a heading-row reader (`Excel::toArray`/`ToCollection`). Then:
   - Collect all ICAOs across rows → `AirportImporter::ensure()` (reuse Commit 1). Record auto-added.
   - For each row build state via shared validation (below): airports now resolved → only genuinely bad rows stay invalid.
   - Default `selected` = all valid row indices.
2. **Review table** — `resources/views/livewire/booking/admin/import.blade.php`:
   - One row per parsed flight; checkbox bound to `selected` (use existing `components/forms/inputs/checkbox.blade.php`).
   - **Errored rows: checkbox disabled**, show red error badge(s) per row.
   - Select all / select none controls (toggle valid indices; Alpine or Livewire methods `selectAll()`/`selectNone()`).
   - Show columns (origin/dest or airport legs, callsign, actype, ctot/eta, ...) + auto-added-airports notice.
3. **Import** — `import()`:
   - Take `selected` ∩ valid rows; for each, create `Booking` (+ `Flight`s) via the shared action below.
   - `activity()->...->log('Import triggered')` (preserve existing logging from controller `store`).
   - `flashMessage('success', ...)`; `redirect()->route('events.bookings.index', $event)` (mirror current behaviour).

### New: `app/Actions/CreateBookingFromImportRow.php`
Extract booking+flight creation from `BookingsImport::model()` (`app/Imports/BookingsImport.php:32-78`, incl. `getTime()` helper at :109-126) into a reusable action: `__invoke(Event $event, array $row): Booking`. Both single-flight and multi-flight branches. The Livewire `import()` uses it.

### Shared row validation
Extract the per-event rules (`BookingsImport::rules()` at `:90-107`) into a reusable helper, e.g. static `BookingImportRules::for(Event $event): array`. The Livewire component validates each row with `Validator::make($row, BookingImportRules::for($event))` to populate `errors`/`valid`. (Airport existence is checked after `AirportImporter::ensure()`, so `exists:airports,icao` reflects post-auto-add state.)

### Routing / view wiring
- `routes/web.php:76` — keep `GET .../bookings/import` → `BookingImportController@create`, returning `event/admin/import.blade.php` now containing `<livewire:booking.admin.import :event="$event" />` (plus the existing template-download links + header help).
- **Remove** `POST .../import` (`routes/web.php:77`) and `BookingImportController@store` — Livewire owns the submit now.
- Retire `app/Imports/BookingsImport.php` once its logic is moved to the action (only caller was the controller). Confirm no other references before deleting.

### Tests
- `tests/Feature/Livewire/Booking/Admin/ImportTest.php` (Pest + `Livewire::test`):
  - Upload a fake file (`UploadedFile::fake()->createWithContent(...)`, like `BookingImportControllerTest`) mixing valid + invalid rows; assert `rows` parsed with correct `valid`/`errors`, invalid rows excluded from `selected`.
  - Assert select-all / select-none toggles.
  - Call `import()`; assert only selected valid rows created `Booking`/`Flight` (`assertDatabaseHas`), errored rows skipped, redirect fired.
  - Assert auto-add path: row with unknown-but-in-dataset ICAO → airport created + row valid (fake CSV).

---

## Verification (end-to-end)

1. `php artisan test --compact --filter=AirportImporter` then `--filter=Import` (both new + existing booking import tests pass).
2. Manual via Herd: admin → an event → bookings → Import. Upload a file with: a valid row, a row with a bad time, a row with an ICAO not in the DB but in the dataset, and a row with a garbage ICAO.
   - Expect: review table renders; bad-time + garbage-ICAO rows shown errored & unselectable; the dataset-ICAO row valid (airport auto-created); select a subset → Import → only selected bookings appear on the bookings index; flash success.
3. `vendor/bin/pint --dirty --format agent` before finalizing.

## Out of scope
- Standalone script validator (dropped).
- Removing the scheduled bulk `ImportAirportsJob` (kept).
- Making `airports.iata` nullable (only if blank-iata airports must be importable).
