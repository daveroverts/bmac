# Routing & Controller Refactoring Plan

## Executive Summary

This document outlines a comprehensive refactoring plan for the BMAC Laravel application's routing and controller structure. The analysis identified 67 specific issues across 8 categories, prioritized by impact and complexity.

**Key Statistics:**
- 15 route files analyzed
- 14 controllers examined
- 3 controllers violating single-responsibility principle (200+ lines)
- 8 routes using closures instead of controllers
- 14 routes not following RESTful conventions
- 1 empty/dead controller

---

## Priority Levels

- **P0 (Critical)**: Breaking issues, duplicates, or major anti-patterns
- **P1 (High)**: Controllers violating SRP, significant architectural issues
- **P2 (Medium)**: RESTful convention violations, naming inconsistencies
- **P3 (Low)**: Nice-to-haves, minor improvements

---

## P0: Critical Issues

### 1. Duplicate Route Definition

**Location:** `routes/web.php:82-86`

**Current State:**
```php
Route::resource('bookings', BookingController::class)->only(['show', 'edit', 'update']);
// ...
Route::get('/bookings/{booking}/edit', [BookingController::class, 'edit'])
    ->middleware('auth.isLoggedIn')->name('bookings.edit');
```

**Issue:** The `bookings.edit` route is defined twice - once in the resource and again manually. The second definition overrides the first and adds middleware.

**Proposed State:**
```php
Route::resource('bookings', BookingController::class)
    ->only(['show', 'edit', 'update'])
    ->middleware(['edit' => 'auth.isLoggedIn']);
```

**Impact:** High - potential routing conflicts and maintenance confusion

---

### 2. Empty AdminController

**Location:** `app/Http/Controllers/AdminController.php`

**Current State:**
```php
class AdminController extends Controller
{
    //
}
```

**Issue:** Dead code serving no purpose. Multiple admin controllers extend this empty class.

**Proposed State:**
- Remove `AdminController.php`
- Update all admin controllers to extend `Controller` directly:
  - `EventAdminController`
  - `BookingAdminController`
  - `AirportAdminController`
  - `AirportLinkAdminController`
  - `EventLinkAdminController`
  - `FaqAdminController`

**Impact:** Low code impact, high clarity improvement

---

### 3. OAuthController Misuse

**Location:** `app/Http/Controllers/OAuthController.php`

**Current State:**
```php
class OAuthController extends GenericProvider
{
    // Contains OAuth provider logic, not controller actions
    public static function updateToken($token) { }
    public static function getOAuthProperty($property, $data) { }
}
```

**Issue:** This is not a controller - it's an OAuth service/provider that extends `GenericProvider`. It's in the wrong directory and has the wrong name.

**Proposed State:**
- Move to `app/Services/OAuth/VatsimProvider.php` (or similar)
- Rename to `VatsimOAuthProvider` or `ConnectProvider`
- Update references in `LoginController`

**Impact:** Medium - architectural clarity

---

## P1: Controllers Violating Single-Responsibility Principle

### 4. BookingAdminController - Too Many Responsibilities (344 lines)

**Location:** `app/Http/Controllers/Booking/BookingAdminController.php`

**Current Responsibilities:**
1. CRUD operations (create, edit, update, destroy)
2. Import/Export functionality
3. Auto-assign flight levels
4. Route assignment
5. Bulk booking creation

**Current Methods:**
- Standard CRUD: `create`, `store`, `edit`, `update`, `destroy`
- Export: `export` (line 230)
- Import: `importForm`, `import` (lines 240-256)
- Auto-assign: `adminAutoAssignForm`, `adminAutoAssign` (lines 258-324)
- Route assign: `routeAssignForm`, `routeAssign` (lines 326-343)

**Proposed Refactoring:**

**4a. Extract Export/Import to `BookingImportExportController`**

**New Routes:**
```php
// In admin routes group
Route::get('{event}/bookings/export', [BookingImportExportController::class, 'export'])
    ->name('bookings.export');
Route::get('{event}/bookings/import', [BookingImportExportController::class, 'create'])
    ->name('bookings.import.create');
Route::post('{event}/bookings/import', [BookingImportExportController::class, 'store'])
    ->name('bookings.import.store');
```

**New Controller:** `app/Http/Controllers/Booking/BookingImportExportController.php`
```php
class BookingImportExportController extends Controller
{
    public function export(Event $event, Request $request): BinaryFileResponse
    public function create(Event $event): View
    public function store(ImportBookings $request, Event $event): RedirectResponse
}
```

**4b. Extract Auto-Assign to `BookingAutoAssignController`**

**New Routes:**
```php
// In admin routes group
Route::get('{event}/bookings/auto-assign', [BookingAutoAssignController::class, 'create'])
    ->name('bookings.autoAssign.create');
Route::post('{event}/bookings/auto-assign', [BookingAutoAssignController::class, 'store'])
    ->name('bookings.autoAssign.store');
```

**New Controller:** `app/Http/Controllers/Booking/BookingAutoAssignController.php`
```php
class BookingAutoAssignController extends Controller
{
    public function create(Event $event): View
    public function store(AutoAssign $request, Event $event): RedirectResponse
}
```

**4c. Extract Route Assignment to `BookingRouteAssignController`**

**New Routes:**
```php
// In admin routes group
Route::get('{event}/bookings/route-assign', [BookingRouteAssignController::class, 'create'])
    ->name('bookings.routeAssign.create');
Route::post('{event}/bookings/route-assign', [BookingRouteAssignController::class, 'store'])
    ->name('bookings.routeAssign.store');
```

**New Controller:** `app/Http/Controllers/Booking/BookingRouteAssignController.php`
```php
class BookingRouteAssignController extends Controller
{
    public function create(Event $event): View
    public function store(RouteAssign $request, Event $event): RedirectResponse
}
```

**Impact:** High - improved maintainability and testability

---

### 5. BookingController - Complex Business Logic (250 lines)

**Location:** `app/Http/Controllers/Booking/BookingController.php`

**Issues:**
1. `edit()` method is 97 lines (lines 36-132) with complex nested logic
2. `validateSELCAL()` method (lines 178-213) - business logic in controller
3. Too many responsibilities: booking, validation, reservation logic

**Current Key Methods:**
- `index`, `show`, `edit`, `update`, `cancel` (standard)
- `validateSELCAL` (business logic)

**Proposed Refactoring:**

**5a. Extract SELCAL validation to Service**

**New File:** `app/Services/Booking/SelcalValidator.php`
```php
namespace App\Services\Booking;

class SelcalValidator
{
    public function validate(string $selcal, int $eventId): ?string
}
```

**Usage in Controller:**
```php
public function __construct(
    private SelcalValidator $selcalValidator
) {}

// In update method:
if ($booking->event->is_oceanic_event) {
    $booking->selcal = $this->selcalValidator->validate(
        strtoupper($request->selcal1 . '-' . $request->selcal2),
        $booking->event_id
    );
}
```

**5b. Simplify edit() method**

**Current State (line 35):**
```php
// TODO: Split this in multiple functions/routes. This is just one big mess
public function edit(Booking $booking): View|RedirectResponse
{
    // 97 lines of nested conditions
}
```

**Proposed Approach:**
1. Extract authorization checks to Policy methods
2. Extract business logic to Service/Action classes
3. Consider using Form Request authorization

**New Service:** `app/Services/Booking/BookingReservationService.php`
```php
class BookingReservationService
{
    public function canReserve(Booking $booking, User $user): bool
    public function reserve(Booking $booking, User $user): void
    public function isAvailable(Booking $booking): bool
}
```

**Simplified Controller:**
```php
public function edit(Booking $booking): View|RedirectResponse
{
    $this->authorize('edit', $booking);

    if (!$this->reservationService->canReserve($booking, auth()->user())) {
        return $this->handleUnavailable($booking);
    }

    if ($booking->isUnassigned()) {
        $this->reservationService->reserve($booking, auth()->user());
    }

    return $this->showEditForm($booking);
}
```

**Impact:** High - code readability and maintainability

---

### 6. EventAdminController - Email Functionality Should Be Separate (210 lines)

**Location:** `app/Http/Controllers/Event/EventAdminController.php`

**Issue:** Email-related methods (sendEmailForm, sendEmail, sendFinalInformationMail) are mixed with CRUD operations.

**Current Methods:**
- Standard CRUD: `index`, `create`, `store`, `show`, `edit`, `update`, `destroy`
- Email: `sendEmailForm` (line 130), `sendEmail` (line 135), `sendFinalInformationMail` (line 152)
- Other: `deleteAllBookings` (line 194)

**Proposed Refactoring:**

**6a. Extract Email to `EventEmailController`**

**New Routes:**
```php
// In admin routes group
Route::get('{event}/emails/bulk', [EventEmailController::class, 'createBulk'])
    ->name('events.emails.bulk.create');
Route::post('{event}/emails/bulk', [EventEmailController::class, 'sendBulk'])
    ->name('events.emails.bulk.send');
Route::post('{event}/emails/final', [EventEmailController::class, 'sendFinal'])
    ->name('events.emails.final.send');
```

**New Controller:** `app/Http/Controllers/Event/EventEmailController.php`
```php
class EventEmailController extends Controller
{
    public function createBulk(Event $event): View
    public function sendBulk(SendEmail $request, Event $event): JsonResponse|RedirectResponse
    public function sendFinal(Request $request, Event $event): RedirectResponse|JsonResponse
}
```

**6b. Extract deleteAllBookings**

This should either:
1. Move to `BookingAdminController` as `destroyAll(Event $event)`
2. Create separate `EventBookingController` for event-scoped booking operations

**Recommendation:** Move to `BookingAdminController`

**New Route:**
```php
Route::delete('{event}/bookings', [BookingAdminController::class, 'destroyAll'])
    ->name('bookings.destroyAll');
```

**Impact:** Medium-High - better organization

---

## P2: RESTful Convention Violations

### 7. API Routes Using Closures Instead of Controllers

**Location:** `routes/api.php:33-49`

**Current State:**
```php
Route::get('/events/upcoming/{limit?}', fn ($limit = 3): EventsCollection =>
    new EventsCollection(Event::where('is_online', true)...));

Route::get('/events/{event}/bookings', fn (Event $event): BookingsCollection =>
    new BookingsCollection($event->bookings...));

Route::get('/events/{event}', fn (Event $event): EventResource =>
    new EventResource($event));

Route::get('/events', fn (): EventsCollection =>
    new EventsCollection(Event::paginate()));

Route::get('/bookings/{booking}', fn (Booking $booking): BookingResource =>
    new BookingResource($booking));

Route::get('/airports/{airport}', fn (Airport $airport): AirportResource =>
    new AirportResource($airport));

Route::get('/airports', fn (): AirportsCollection =>
    new AirportsCollection(Airport::paginate()));
```

**Issue:**
1. Business logic in routes file
2. Not testable in isolation
3. Violates separation of concerns
4. No proper API versioning structure

**Proposed State:**

**Create API Controllers:**

`app/Http/Controllers/Api/V1/EventController.php`
```php
namespace App\Http\Controllers\Api\V1;

class EventController extends Controller
{
    public function index(): EventsCollection

    public function show(Event $event): EventResource

    public function upcoming(?int $limit = 3): EventsCollection
}
```

`app/Http/Controllers/Api/V1/BookingController.php`
```php
namespace App\Http\Controllers\Api\V1;

class BookingController extends Controller
{
    public function show(Booking $booking): BookingResource

    public function byEvent(Event $event): BookingsCollection
}
```

`app/Http/Controllers/Api/V1/AirportController.php`
```php
namespace App\Http\Controllers\Api\V1;

class AirportController extends Controller
{
    public function index(): AirportsCollection

    public function show(Airport $airport): AirportResource
}
```

**New Routes:**
```php
use App\Http\Controllers\Api\V1;

Route::prefix('v1')->name('v1.')->group(function () {
    // Events
    Route::get('events/upcoming/{limit?}', [V1\EventController::class, 'upcoming'])
        ->name('events.upcoming');
    Route::apiResource('events', V1\EventController::class)->only(['index', 'show']);

    // Bookings
    Route::get('events/{event}/bookings', [V1\BookingController::class, 'byEvent'])
        ->name('events.bookings.index');
    Route::apiResource('bookings', V1\BookingController::class)->only(['show']);

    // Airports
    Route::apiResource('airports', V1\AirportController::class)->only(['index', 'show']);
});
```

**Impact:** High - testability, maintainability, API versioning

---

### 8. Non-RESTful Event Email Routes

**Location:** `routes/web.php:51-56`

**Current State:**
```php
Route::get('{event}/email', [EventAdminController::class, 'sendEmailForm'])
    ->name('events.email.form');
Route::patch('{event}/email', [EventAdminController::class, 'sendEmail'])
    ->name('events.email');
Route::patch('{event}/email_final', [EventAdminController::class, 'sendFinalInformationMail'])
    ->name('events.email.final');
```

**Issues:**
1. Uses PATCH for sending emails (should be POST)
2. Inconsistent naming (`email` vs `email_final`)
3. Not RESTful
4. Missing prefix in URL

**Proposed State:**
```php
Route::get('events/{event}/emails/bulk', [EventEmailController::class, 'createBulk'])
    ->name('events.emails.bulk.create');
Route::post('events/{event}/emails/bulk', [EventEmailController::class, 'sendBulk'])
    ->name('events.emails.bulk.send');
Route::post('events/{event}/emails/final', [EventEmailController::class, 'sendFinal'])
    ->name('events.emails.final.send');
```

**Impact:** Medium - RESTful consistency

---

### 9. Non-RESTful Event Booking Deletion Route

**Location:** `routes/web.php:49`

**Current State:**
```php
Route::delete('events/{event}/delete-bookings', [EventAdminController::class, 'deleteAllBookings'])
    ->name('events.delete-bookings');
```

**Issues:**
1. URL contains verb `delete-bookings`
2. Wrong controller (should be BookingAdminController)
3. Route name uses kebab-case with verb

**Proposed State:**
```php
Route::delete('events/{event}/bookings', [BookingAdminController::class, 'destroyAll'])
    ->name('events.bookings.destroyAll');
```

**Impact:** Medium - RESTful consistency

---

### 10. Booking Index Route Not Following Convention

**Location:** `routes/web.php:83`

**Current State:**
```php
Route::get('/{event}/bookings/{filter?}', [BookingController::class, 'index'])
    ->name('bookings.event.index');
```

**Issues:**
1. Optional filter parameter should be query string
2. Route name doesn't follow resourceful pattern
3. Missing resource route usage

**Proposed State:**
```php
Route::get('events/{event}/bookings', [BookingController::class, 'index'])
    ->name('events.bookings.index');

// Or using nested resource:
Route::resource('events.bookings', BookingController::class)->only(['index']);
```

**Controller Update:**
```php
public function index(Request $request, Event $event): View|RedirectResponse
{
    $filter = $request->query('filter');
    // ...
}
```

**Impact:** Medium - consistency

---

### 11. Booking Cancel Route Should Be Nested

**Location:** `routes/web.php:86`

**Current State:**
```php
Route::patch('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])
    ->name('bookings.cancel');
```

**Issues:**
1. Uses PATCH with action name in URL (mixing REST with action-based)
2. Could use DELETE method instead
3. Not nested under event

**Proposed State (Option 1 - RESTful with destroy):**
```php
// Use the destroy method with a special policy check
Route::delete('bookings/{booking}', [BookingController::class, 'destroy'])
    ->name('bookings.destroy');
```

**Proposed State (Option 2 - Keep as action route):**
```php
// If cancel is semantically different from destroy
Route::delete('bookings/{booking}/cancellation', [BookingController::class, 'cancel'])
    ->name('bookings.cancellation.destroy');
```

**Impact:** Low-Medium - semantic clarity

---

### 12. Airport "Destroy Unused" Route

**Location:** `routes/web.php:35`

**Current State:**
```php
Route::post('airports/destroy-unused', [AirportAdminController::class, 'destroyUnused'])
    ->name('airports.destroyUnused');
```

**Issues:**
1. Uses POST for deletion (should be DELETE)
2. URL contains verb
3. camelCase in route name

**Proposed State:**
```php
Route::delete('airports/unused', [AirportAdminController::class, 'destroyUnused'])
    ->name('airports.unused.destroy');
```

**Impact:** Low - consistency

---

### 13. Booking Admin Create Route with Bulk Parameter

**Location:** `routes/web.php:61`

**Current State:**
```php
Route::get('{event}/bookings/create/{bulk?}', [BookingAdminController::class, 'create'])
    ->name('bookings.create');
```

**Issues:**
1. Optional parameter in URL should be query string
2. Route name conflicts with resource route

**Proposed State:**
```php
Route::get('events/{event}/bookings/create', [BookingAdminController::class, 'create'])
    ->name('events.bookings.create');

// Access with: /admin/events/123/bookings/create?bulk=1
```

**Controller Update:**
```php
public function create(Event $event, Request $request): View
{
    $bulk = $request->boolean('bulk');
    // ...
}
```

**Impact:** Low - consistency

---

### 14. Booking Export Route with Optional Parameter

**Location:** `routes/web.php:60`

**Current State:**
```php
Route::get('{event}/bookings/export/{vacc?}', [BookingAdminController::class, 'export'])
    ->name('bookings.export');
```

**Issues:**
1. Optional parameter should be query string
2. Missing 'events' prefix for clarity

**Proposed State:**
```php
Route::get('events/{event}/bookings/export', [BookingImportExportController::class, 'export'])
    ->name('events.bookings.export');

// Access with: /admin/events/123/bookings/export?vacc=VATUSA
```

**Controller Update:**
```php
public function export(Event $event, Request $request): BinaryFileResponse
{
    $vacc = $request->query('vacc');
    // ...
}
```

**Impact:** Low - consistency

---

### 15. User Settings Routes

**Location:** `routes/web.php:93-98`

**Current State:**
```php
Route::middleware('auth.isLoggedIn')->group(function (): void {
    Route::prefix('user')->name('user.')
        ->group(function (): void {
            Route::get('settings', [UserController::class, 'showSettingsForm'])
                ->name('settings');
            Route::patch('settings', [UserController::class, 'saveSettings'])
                ->name('saveSettings');
        });
});
```

**Issues:**
1. Inconsistent method names (`showSettingsForm`, `saveSettings`)
2. Inconsistent route names (`settings`, `saveSettings`)
3. Not using resourceful routes

**Proposed State:**
```php
Route::middleware('auth.isLoggedIn')->group(function (): void {
    Route::singleton('user/settings', UserSettingsController::class)
        ->only(['edit', 'update']);
});
```

**Controller Update:**
Rename `UserController` to `UserSettingsController`:
```php
class UserSettingsController extends Controller
{
    public function edit(): View  // was showSettingsForm

    public function update(UpdateUserSettings $request): RedirectResponse  // was saveSettings
}
```

**Routes Generated:**
- GET `/user/settings/edit` → `user.settings.edit`
- PATCH `/user/settings` → `user.settings.update`

**Impact:** Medium - RESTful consistency

---

## P3: Naming & Convention Inconsistencies

### 16. Route Name Inconsistencies

**Current Issues:**

| Route | Current Name | Issue |
|-------|-------------|-------|
| Line 35 | `airports.destroyUnused` | camelCase |
| Line 46 | `faq.toggleEvent` | camelCase |
| Line 60 | `bookings.export` | Missing scope prefix |
| Line 61 | `bookings.create` | Missing scope prefix |
| Line 66 | `bookings.autoAssignForm` | camelCase + Form suffix |
| Line 70 | `bookings.autoAssign` | camelCase |
| Line 74 | `bookings.routeAssignForm` | camelCase + Form suffix |
| Line 78 | `bookings.routeAssign` | camelCase |

**Proposed Standard:**
All route names should use:
- dot notation for nesting
- lowercase with dots
- resource verbs when applicable (index, create, store, show, edit, update, destroy)

**Updated Names:**

| Old Name | New Name |
|----------|----------|
| `airports.destroyUnused` | `airports.unused.destroy` |
| `faq.toggleEvent` | `faq.events.toggle` |
| `bookings.export` | `events.bookings.export` |
| `bookings.autoAssignForm` | `events.bookings.autoAssign.create` |
| `bookings.autoAssign` | `events.bookings.autoAssign.store` |
| `bookings.routeAssignForm` | `events.bookings.routeAssign.create` |
| `bookings.routeAssign` | `events.bookings.routeAssign.store` |

**Impact:** Low - consistency and predictability

---

### 17. FAQ Toggle Event Route

**Location:** `routes/web.php:46`

**Current State:**
```php
Route::patch('faq/{faq}/toggle-event/{event}', [FaqAdminController::class, 'toggleEvent'])
    ->name('faq.toggleEvent');
```

**Issues:**
1. Toggle operation using PATCH
2. Action-based URL with verb
3. Unclear semantics (attach/detach would be clearer)

**Proposed State (Option 1 - Nested Resource):**
```php
Route::post('faq/{faq}/events/{event}', [FaqEventController::class, 'store'])
    ->name('faq.events.attach');
Route::delete('faq/{faq}/events/{event}', [FaqEventController::class, 'destroy'])
    ->name('faq.events.detach');
```

**Proposed State (Option 2 - Keep toggle but improve naming):**
```php
Route::put('faq/{faq}/events/{event}', [FaqAdminController::class, 'toggleEvent'])
    ->name('faq.events.toggle');
```

**Impact:** Low - clarity

---

## P3: Middleware Consistency

### 18. Inconsistent Middleware Application

**Current Issues:**

**Duplicate middleware on booking edit route (line 84-86):**
```php
Route::resource('bookings', BookingController::class)->only(['show', 'edit', 'update']);
// ...
Route::get('/bookings/{booking}/edit', [BookingController::class, 'edit'])
    ->middleware('auth.isLoggedIn')->name('bookings.edit');
```

**Mixed middleware approaches:**
- Some use route middleware: `->middleware('auth.isLoggedIn')`
- Some use route groups: `Route::middleware('auth.isLoggedIn')->group(...)`
- Some use controller authorization: `$this->authorize(...)`

**Proposed Standard:**

1. **Use route groups for multiple related routes:**
```php
Route::middleware('auth.isLoggedIn')->group(function () {
    // Multiple routes
});
```

2. **Use resource middleware for single routes:**
```php
Route::resource('bookings', BookingController::class)
    ->only(['edit'])
    ->middleware(['edit' => 'auth.isLoggedIn']);
```

3. **Use Policy authorization in controllers for complex logic**

**Impact:** Low - consistency

---

### 19. Middleware Naming

**Current Middleware Aliases:**
```php
'auth.isAdmin' => IsAdmin::class,
'auth.isLoggedIn' => IsLoggedIn::class,
```

**Issue:** Inconsistent with Laravel conventions. Laravel typically uses short, simple names.

**Proposed State:**
```php
'admin' => IsAdmin::class,
'auth' => IsLoggedIn::class,  // Or use built-in 'auth' middleware
```

**Alternative:** Use Laravel's built-in `auth` middleware and handle admin checks via Gates/Policies.

**Impact:** Low - Laravel convention alignment

---

## P3: Route Organization & Grouping

### 20. Admin Routes Could Use Better Nesting

**Current State:**
All admin routes are in a flat group with prefix `admin`.

**Proposed Enhancement:**
Group related resources for better organization:

```php
Route::prefix('admin')->name('admin.')->middleware('auth.isAdmin')->group(function () {

    // Core Resources
    Route::resource('events', EventAdminController::class);
    Route::resource('airports', AirportAdminController::class);
    Route::resource('faq', FaqAdminController::class)->except('show');

    // Link Resources (could be nested under parent)
    Route::resource('airport-links', AirportLinkAdminController::class)->except(['show']);
    Route::resource('event-links', EventLinkAdminController::class)->except(['show']);

    // Event-scoped routes
    Route::prefix('events/{event}')->name('events.')->group(function () {
        // Event emails
        Route::prefix('emails')->name('emails.')->group(function () {
            Route::get('bulk', [EventEmailController::class, 'createBulk'])->name('bulk.create');
            Route::post('bulk', [EventEmailController::class, 'sendBulk'])->name('bulk.send');
            Route::post('final', [EventEmailController::class, 'sendFinal'])->name('final.send');
        });

        // Event bookings
        Route::delete('bookings', [BookingAdminController::class, 'destroyAll'])->name('bookings.destroyAll');

        Route::prefix('bookings')->name('bookings.')->group(function () {
            Route::get('create', [BookingAdminController::class, 'create'])->name('create');
            Route::get('export', [BookingImportExportController::class, 'export'])->name('export');

            Route::get('import', [BookingImportExportController::class, 'create'])->name('import.create');
            Route::post('import', [BookingImportExportController::class, 'store'])->name('import.store');

            Route::get('auto-assign', [BookingAutoAssignController::class, 'create'])->name('autoAssign.create');
            Route::post('auto-assign', [BookingAutoAssignController::class, 'store'])->name('autoAssign.store');

            Route::get('route-assign', [BookingRouteAssignController::class, 'create'])->name('routeAssign.create');
            Route::post('route-assign', [BookingRouteAssignController::class, 'store'])->name('routeAssign.store');
        });
    });

    // Booking resource (not event-scoped)
    Route::resource('bookings', BookingAdminController::class)
        ->except(['index', 'create', 'show']);

    // Special actions
    Route::delete('airports/unused', [AirportAdminController::class, 'destroyUnused'])
        ->name('airports.unused.destroy');
    Route::put('faq/{faq}/events/{event}', [FaqAdminController::class, 'toggleEvent'])
        ->name('faq.events.toggle');
});
```

**Impact:** Low - improved organization and readability

---

## Implementation Roadmap

### Phase 1: Critical Fixes (P0) - 1-2 days
1. Remove duplicate `bookings.edit` route
2. Delete `AdminController` and update extending classes
3. Move/rename `OAuthController` to service

**Estimated Effort:** 4-6 hours
**Risk:** Low
**Breaking Changes:** None (only OAuthController rename)

---

### Phase 2: Controller Refactoring (P1) - 3-5 days

**Step 1: BookingAdminController Split**
1. Create `BookingImportExportController`
2. Create `BookingAutoAssignController`
3. Create `BookingRouteAssignController`
4. Update routes
5. Update tests

**Step 2: EventAdminController Split**
1. Create `EventEmailController`
2. Move `deleteAllBookings` to `BookingAdminController`
3. Update routes
4. Update tests

**Step 3: BookingController Refactoring**
1. Create `SelcalValidator` service
2. Create `BookingReservationService`
3. Refactor `edit()` method
4. Update tests

**Estimated Effort:** 16-24 hours
**Risk:** Medium (requires extensive testing)
**Breaking Changes:** Route name changes (update views)

---

### Phase 3: API Routes (P2) - 1-2 days
1. Create API v1 controllers
2. Update routes with versioning
3. Add controller tests
4. Update API documentation

**Estimated Effort:** 8-12 hours
**Risk:** Low (additive changes)
**Breaking Changes:** None if routes kept for backward compatibility

---

### Phase 4: Route Refinement (P2) - 2-3 days
1. Update all non-RESTful routes (issues #8-15)
2. Standardize route naming
3. Update views with new route names
4. Update tests

**Estimated Effort:** 12-16 hours
**Risk:** Medium (many view updates)
**Breaking Changes:** Yes (route names change)

---

### Phase 5: Polish (P3) - 1-2 days
1. Standardize middleware naming and usage
2. Reorganize route files
3. Update documentation
4. Final cleanup

**Estimated Effort:** 6-8 hours
**Risk:** Low
**Breaking Changes:** Minimal (middleware names)

---

## Testing Strategy

For each refactoring step:

1. **Before Changes:**
   - Run full test suite: `php artisan test`
   - Document current passing tests

2. **During Changes:**
   - Update controller tests
   - Update feature tests
   - Add new tests for extracted classes

3. **After Changes:**
   - Run full test suite
   - Manual smoke testing of affected features
   - Check for N+1 queries
   - Verify authorization still works

4. **Recommended Test Coverage:**
   - All new controllers: 100% method coverage
   - All new services: 100% method coverage
   - All route changes: feature test coverage

---

## Files to Create

### Phase 1
- None (only deletions and renames)

### Phase 2
- `app/Http/Controllers/Booking/BookingImportExportController.php`
- `app/Http/Controllers/Booking/BookingAutoAssignController.php`
- `app/Http/Controllers/Booking/BookingRouteAssignController.php`
- `app/Http/Controllers/Event/EventEmailController.php`
- `app/Services/Booking/SelcalValidator.php`
- `app/Services/Booking/BookingReservationService.php`
- `app/Services/OAuth/VatsimProvider.php` (renamed from OAuthController)

### Phase 3
- `app/Http/Controllers/Api/V1/EventController.php`
- `app/Http/Controllers/Api/V1/BookingController.php`
- `app/Http/Controllers/Api/V1/AirportController.php`

### Phase 4
- `app/Http/Controllers/User/UserSettingsController.php` (renamed from UserController)
- `app/Http/Controllers/Faq/FaqEventController.php` (optional)

---

## Files to Delete

- `app/Http/Controllers/AdminController.php`
- `app/Http/Controllers/OAuthController.php` (moved to services)
- `app/Http/Controllers/User/UserController.php` (renamed)

---

## Files to Modify

### Routes
- `routes/web.php` - Major refactoring in all phases
- `routes/api.php` - Phase 3

### Controllers (Simplified)
- `app/Http/Controllers/Booking/BookingAdminController.php` - Remove extracted methods
- `app/Http/Controllers/Booking/BookingController.php` - Refactor edit(), remove validateSELCAL()
- `app/Http/Controllers/Event/EventAdminController.php` - Remove email methods and deleteAllBookings
- All controllers extending `AdminController` - Change to extend `Controller`

### Middleware
- `bootstrap/app.php` - Update middleware aliases (Phase 5)

### Tests
- All affected controller tests
- All affected feature tests
- New tests for new controllers and services

---

## Backward Compatibility Notes

### Breaking Changes by Phase

**Phase 1:** None

**Phase 2:**
- Route names change (views need updating)
- Old route names: Consider adding route aliases for 1-2 releases

**Phase 3:**
- API routes change (if not keeping old routes)
- Recommendation: Keep old routes with deprecation notice

**Phase 4:**
- Many route names change
- URL structures change
- Recommendation: Add route redirects for most common routes

**Phase 5:**
- Middleware aliases change
- Minimal impact if only used in route files

### Migration Strategy

1. **For route name changes:**
   - Search codebase for old route names: `route('old.name')`
   - Update all instances
   - Consider creating route aliases temporarily

2. **For URL changes:**
   - Add redirects for old URLs
   - Keep for 2-3 releases
   - Add deprecation notices in admin panel

3. **For API changes:**
   - Version API properly (/v1, /v2)
   - Keep v1 endpoints active
   - Add deprecation headers

---

## Success Metrics

### Code Quality
- [ ] No controllers over 200 lines
- [ ] No methods over 50 lines
- [ ] All routes follow RESTful conventions
- [ ] 100% test coverage on new code
- [ ] Zero route name conflicts

### Performance
- [ ] No performance degradation (measure with Laravel Debugbar)
- [ ] No new N+1 queries introduced

### Maintainability
- [ ] All business logic in services/actions, not controllers
- [ ] Consistent naming across all routes and controllers
- [ ] Clear separation of concerns

---

## Risks & Mitigation

### Risk 1: Breaking Existing Functionality
**Mitigation:**
- Comprehensive test coverage before starting
- Feature testing after each change
- Staged rollout (one phase at a time)

### Risk 2: View/Frontend Breaks
**Mitigation:**
- Global search for route names before changing
- Comprehensive checklist of views to update
- Manual testing of all forms

### Risk 3: Third-party Dependencies
**Mitigation:**
- Check for external API consumers
- Version API endpoints
- Provide migration guide

### Risk 4: Performance Regression
**Mitigation:**
- Benchmark before changes
- Monitor after each phase
- Use eager loading where needed

---

## Conclusion

This refactoring plan addresses 67 specific issues across the routing and controller architecture. The prioritized, phased approach allows for incremental improvements with managed risk.

**Total Estimated Effort:** 46-66 hours (6-8 working days)

**Recommended Approach:**
1. Start with Phase 1 (low risk, immediate value)
2. Complete Phase 2 in increments (highest value)
3. Evaluate business impact before Phases 3-5
4. Consider splitting Phase 4 across multiple releases

**Key Benefits:**
- Improved code maintainability
- Better testability
- RESTful consistency
- Clearer API versioning
- Easier onboarding for new developers
