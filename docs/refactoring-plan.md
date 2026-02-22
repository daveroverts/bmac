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

**4a. Extract Export to `BookingExportController`**

**New Route:**
```php
// In admin routes group
Route::get('{event}/bookings/export', BookingExportController::class)
    ->name('bookings.export');
```

**New Controller:** `app/Http/Controllers/Booking/BookingExportController.php`
```php
class BookingExportController extends Controller
{
    public function __invoke(Event $event, Request $request): BinaryFileResponse
}
```

**4b. Extract Import to `BookingImportController`**

**New Routes:**
```php
// In admin routes group
Route::get('{event}/bookings/import', [BookingImportController::class, 'create'])
    ->name('bookings.import.create');
Route::post('{event}/bookings/import', [BookingImportController::class, 'store'])
    ->name('bookings.import.store');
```

**New Controller:** `app/Http/Controllers/Booking/BookingImportController.php`
```php
class BookingImportController extends Controller
{
    public function create(Event $event): View
    public function store(ImportBookings $request, Event $event): RedirectResponse
}
```

**4c. Extract Auto-Assign to `BookingAutoAssignController`**

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

**4d. Extract Route Assignment to `BookingRouteAssignController`**

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

**5b. Split Edit into Reserve and Edit Endpoints**

**Current State (line 35):**
```php
// TODO: Split this in multiple functions/routes. This is just one big mess
public function edit(Booking $booking): View|RedirectResponse
{
    // 97 lines mixing reservation logic with edit form display
}
```

**Issue:** The `edit()` method conflates two distinct user actions:
1. **Reserving/claiming** an unassigned booking (creating a relationship)
2. **Editing** an already-assigned booking (modifying existing data)

**Understanding Booking States & Constraints:**

The booking system has three states with specific timing and editability constraints:

```
UNASSIGNED → (reserve) → RESERVED → (confirm) → BOOKED → (cancel) → UNASSIGNED
```

**State Definitions:**
- **UNASSIGNED**: No user assigned, available for reservation
- **RESERVED**: User assigned, status=RESERVED, expires in 10 minutes if not confirmed
- **BOOKED**: User assigned, status=BOOKED, confirmed booking

**Key Constraints:**

1. **Booking Window (`event.endBooking`)**: Hard deadline for all booking operations
   - Reserve: ❌ Not allowed after `endBooking`
   - Confirm: ❌ Not allowed after `endBooking`
   - Edit: ❌ **Never allowed** after `endBooking` (even if `is_editable=true`)
   - Cancel: ❌ Not allowed after `endBooking`
   - **Reason**: Admins export data after this point for external tooling

2. **`is_editable` Flag**: Database column controlling whether callsign/acType can be modified
   - Set when booking is created (typically false for real-life events)
   - Only applies to BOOKED status
   - Only matters **before** `endBooking` (booking window takes precedence)
   - **Edit logic**: `is_editable=true` AND `event.endBooking > now()` → can edit callsign/acType

**Proposed Approach:**

**5b-1. Create Reservation Endpoint**

**New Route:**
```php
Route::post('bookings/{booking}/reservation', [BookingReservationController::class, 'store'])
    ->middleware('auth.isLoggedIn')
    ->name('bookings.reservation.store');
```

**New Controller:** `app/Http/Controllers/Booking/BookingReservationController.php`
```php
class BookingReservationController extends Controller
{
    public function store(Booking $booking): RedirectResponse
    {
        $this->authorize('reserve', $booking);

        // Check if booking window is still open
        if ($booking->event->endBooking < now()) {
            flashMessage('danger', __('Danger'), __('Bookings have been closed'));
            return redirect()->route('bookings.event.index', $booking->event);
        }

        // Check if user already has a reservation
        if (auth()->user()->bookings()
            ->where('event_id', $booking->event_id)
            ->where('status', BookingStatus::RESERVED)
            ->exists()) {
            flashMessage('danger', __('Warning'), __('You already have a reservation!'));
            return redirect()->route('bookings.event.index', $booking->event);
        }

        // Check if event allows multiple bookings
        if (!$booking->event->multiple_bookings_allowed
            && auth()->user()->bookings()
                ->where('event_id', $booking->event_id)
                ->where('status', BookingStatus::BOOKED)
                ->exists()) {
            flashMessage('danger', __('Warning'), __('You already have a booking!'));
            return redirect()->route('bookings.event.index', $booking->event);
        }

        // Reserve the booking
        $booking->status = BookingStatus::RESERVED;
        $booking->user()->associate(auth()->user())->save();

        activity()
            ->by(auth()->user())
            ->on($booking)
            ->log('Flight reserved');

        flashMessage(
            'info',
            __('Slot reserved'),
            __('Slot remains reserved until :time', [
                'time' => $booking->updated_at->addMinutes(10)->format('Hi') . 'z'
            ])
        );

        return redirect()->route('bookings.edit', $booking);
    }
}
```

**5b-2. Simplify Edit Endpoint**

**Updated Controller:**
```php
public function edit(Booking $booking): View
{
    $this->authorize('edit', $booking);

    // Check booking window (hard lock after endBooking)
    if ($booking->event->endBooking < now()) {
        flashMessage(
            'danger',
            __('Danger'),
            __('Bookings have been locked at :time', [
                'time' => $booking->event->endBooking->format('d-m-Y Hi') . 'z'
            ])
        );
        return redirect()->route('bookings.event.index', $booking->event);
    }

    // Check if editable for BOOKED status
    if ($booking->status === BookingStatus::BOOKED && !$booking->is_editable) {
        flashMessage('info', __('Danger'), __('You cannot edit the booking!'));
        return redirect()->route('bookings.event.index', $booking->event);
    }

    // Show edit form
    if ($booking->event->event_type_id == EventType::MULTIFLIGHTS->value) {
        return view('booking.edit_multiflights', ['booking' => $booking]);
    }

    $flight = $booking->flights->first();
    return view('booking.edit', ['booking' => $booking, 'flight' => $flight]);
}
```

**5b-3. Update Update Method**

**Updated Controller:**
```php
public function update(UpdateBooking $request, Booking $booking): RedirectResponse
{
    $this->authorize('update', $booking);

    // Check booking window (hard lock after endBooking)
    if ($booking->event->endBooking < now()) {
        abort(403, 'Bookings have been locked');
    }

    // Only update callsign/acType if editable
    if ($booking->is_editable) {
        $booking->fill([
            'callsign' => $request->callsign,
            'acType' => $request->acType
        ]);
    }

    // Handle SELCAL for oceanic events
    if ($booking->event->is_oceanic_event && $request->filled('selcal')) {
        $booking->selcal = $request->selcal;
    }

    // Transition RESERVED → BOOKED
    if ($booking->status === BookingStatus::RESERVED) {
        $booking->status = BookingStatus::BOOKED;
        $booking->save();
        event(new BookingConfirmed($booking));

        flashMessage(
            'success',
            __('Booking confirmed!'),
            __('Your booking has been confirmed.')
        );
    } else {
        $booking->save();
        flashMessage('success', __('Booking edited!'), __('Booking has been edited!'));
    }

    return redirect()->route('bookings.event.index', $booking->event);
}
```

**5b-4. Update Policy**

Add separate authorization methods in `BookingPolicy`:
```php
public function reserve(User $user, Booking $booking): bool
{
    // Can only reserve unassigned bookings within booking window
    return $booking->status === BookingStatus::UNASSIGNED
        && $booking->event->startBooking <= now()
        && $booking->event->endBooking >= now();
}

public function edit(User $user, Booking $booking): bool
{
    // Must own the booking
    if ($booking->user_id !== $user->id) {
        return false;
    }

    // Hard lock after endBooking
    if ($booking->event->endBooking < now()) {
        return false;
    }

    // RESERVED status: always editable (to complete booking)
    if ($booking->status === BookingStatus::RESERVED) {
        return true;
    }

    // BOOKED status: only if is_editable flag is true
    if ($booking->status === BookingStatus::BOOKED) {
        return $booking->is_editable;
    }

    return false;
}

public function update(User $user, Booking $booking): bool
{
    return $this->edit($user, $booking);
}
```

**Benefits:**
- Clearer semantics: POST for reservation (creating), GET for editing (reading)
- Single Responsibility: Each endpoint does one thing
- Better authorization: Separate policies for `reserve` vs `edit` with explicit timing constraints
- Easier testing: Test reservation logic independently from edit form
- More RESTful: Follows resource creation patterns
- **Security**: GET requests no longer modify state (no auto-reservation)

**User Flow:**
1. User clicks "Reserve" button → POST to `/bookings/{booking}/reservation`
2. System validates, reserves booking (status=RESERVED), redirects to `/bookings/{booking}/edit`
3. User fills form, submits → PATCH to `/bookings/{booking}` (status changes RESERVED → BOOKED)
4. User can edit if BOOKED and `is_editable=true` (only before `endBooking`)
5. After `endBooking`: All edit/cancel operations blocked

**Impact:** High - code readability, maintainability, semantic clarity, and security

---

**5c. Extract Cancel to Dedicated Controller**

**Current State (line 175):**
```php
public function cancel(Booking $booking): RedirectResponse
{
    $this->authorize('cancel', $booking);
    // 30+ lines of cancellation logic
}
```

**Issue:** Cancel is a state transition (like reserve), not a traditional CRUD operation. For consistency with extracting reservation, it should have its own controller.

**New Route:**
```php
Route::delete('bookings/{booking}/cancellation', [BookingCancellationController::class, 'destroy'])
    ->middleware('auth.isLoggedIn')
    ->name('bookings.cancellation.destroy');
```

**New Controller:** `app/Http/Controllers/Booking/BookingCancellationController.php`
```php
class BookingCancellationController extends Controller
{
    public function destroy(Booking $booking): RedirectResponse
    {
        $this->authorize('cancel', $booking);

        // Check booking window
        if ($booking->event->endBooking < now()) {
            flashMessage(
                'danger',
                __('Danger'),
                __('Bookings have been locked at :time', [
                    'time' => $booking->event->endBooking->format('d-m-Y Hi') . 'z'
                ])
            );
            return redirect()->route('bookings.event.index', $booking->event);
        }

        // Clear booking data if editable
        if ($booking->is_editable) {
            $booking->fill([
                'callsign' => null,
                'acType' => null,
                'selcal' => null,
            ]);
        }

        // Fire event if was BOOKED
        if ($booking->status === BookingStatus::BOOKED) {
            event(new BookingCancelled($booking, auth()->user()));
            $title = __('Booking cancelled!');
            $message = __('Booking has been cancelled!');
        } else {
            $title = __('Slot free');
            $message = __('Slot is now free to use again');
        }

        // Reset to UNASSIGNED
        $booking->status = BookingStatus::UNASSIGNED;
        $booking->user()->dissociate()->save();

        flashMessage('info', $title, $message);

        return redirect()->route('bookings.event.index', $booking->event);
    }
}
```

**Policy Update:**

Add to `BookingPolicy`:
```php
public function cancel(User $user, Booking $booking): bool
{
    // Must own the booking
    if ($booking->user_id !== $user->id) {
        return false;
    }

    // Can only cancel RESERVED or BOOKED bookings
    if (!in_array($booking->status, [BookingStatus::RESERVED, BookingStatus::BOOKED])) {
        return false;
    }

    // Hard lock after endBooking
    return $booking->event->endBooking >= now();
}
```

**Benefits:**
- Consistent with reservation extraction (both are state transitions)
- Clean separation: `BookingController` becomes pure CRUD
- Clear RESTful semantics: DELETE for cancellation
- Dedicated policy for cancel authorization
- Easier testing and maintenance

**BookingController Final Responsibilities:**
After extracting reservation and cancellation, `BookingController` has clean CRUD operations:
- `index`: List bookings for an event
- `show`: View single booking details
- `edit`: Show edit form (with authorization checks)
- `update`: Save changes to booking

**Impact:** Medium - consistency and separation of concerns

---

### 6. EventAdminController - Email Functionality Should Be Separate ✅ COMPLETE

**Location:** `app/Http/Controllers/Event/EventAdminController.php`

**Issue:** Email-related methods (sendEmailForm, sendEmail, sendFinalInformationMail) are mixed with CRUD operations.

**Implemented Refactoring:**

**6a. Extract Email to `EventEmailController`**

**New Routes:**
```php
Route::get('events/{event}/emails/bulk', [EventEmailController::class, 'createBulk'])
    ->name('events.emails.bulk.create');
Route::post('events/{event}/emails/bulk', [EventEmailController::class, 'sendBulk'])
    ->name('events.emails.bulk.send');
Route::post('events/{event}/emails/final', [EventEmailController::class, 'sendFinal'])
    ->name('events.emails.final.send');
```

**New Controller:** `app/Http/Controllers/Event/EventEmailController.php`
- `createBulk(Event $event): View`
- `sendBulk(SendEmail $request, Event $event): JsonResponse|RedirectResponse`
- `sendFinal(Request $request, Event $event): RedirectResponse|JsonResponse`

Route methods changed from PATCH to POST. Views updated to use new route names.

**6b. Move `deleteAllBookings` to `BookingAdminController::destroyAll`**

**New Route:**
```php
Route::delete('events/{event}/bookings', [BookingAdminController::class, 'destroyAll'])
    ->name('events.bookings.destroyAll');
```

`EventAdminController` is now pure CRUD (113 lines). All extracted tests moved to `EventEmailControllerTest` and `BookingAdminControllerTest`.

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

### 8. Non-RESTful Event Email Routes ✅ COMPLETE

**Previous State:**
```php
Route::get('{event}/email', [EventAdminController::class, 'sendEmailForm'])
    ->name('events.email.form');
Route::patch('{event}/email', [EventAdminController::class, 'sendEmail'])
    ->name('events.email');
Route::patch('{event}/email_final', [EventAdminController::class, 'sendFinalInformationMail'])
    ->name('events.email.final');
```

**Implemented State:**
```php
Route::get('events/{event}/emails/bulk', [EventEmailController::class, 'createBulk'])
    ->name('events.emails.bulk.create');
Route::post('events/{event}/emails/bulk', [EventEmailController::class, 'sendBulk'])
    ->name('events.emails.bulk.send');
Route::post('events/{event}/emails/final', [EventEmailController::class, 'sendFinal'])
    ->name('events.emails.final.send');
```

Methods changed from PATCH to POST, routes moved to `EventEmailController`, URL structure updated to `events/{event}/emails/*`.

**Impact:** Medium - RESTful consistency

---

### 9. Non-RESTful Event Booking Deletion Route ✅ COMPLETE

**Previous State:**
```php
Route::delete('events/{event}/delete-bookings', [EventAdminController::class, 'deleteAllBookings'])
    ->name('events.delete-bookings');
```

**Implemented State:**
```php
Route::delete('events/{event}/bookings', [BookingAdminController::class, 'destroyAll'])
    ->name('events.bookings.destroyAll');
```

Logic moved from `EventAdminController` to `BookingAdminController::destroyAll`. URL verb removed, route name corrected.

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

### 11. Booking Cancel Route Should Be Nested ✅ COMPLETE

**Previous State:**
```php
Route::patch('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])
    ->name('bookings.cancel');
```

**Implemented State:**
```php
Route::delete('bookings/{booking}/cancellation', [BookingCancellationController::class, 'destroy'])
    ->middleware('auth.isLoggedIn')
    ->name('bookings.cancellation.destroy');
```

Cancel logic was extracted to `BookingCancellationController`, the route method changed from PATCH to DELETE, and all views updated to use the new route name.

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
Route::get('events/{event}/bookings/export', BookingExportController::class)
    ->name('events.bookings.export');

// Access with: /admin/events/123/bookings/export?vacc=VATUSA
```

**Controller Update:**
```php
public function __invoke(Event $event, Request $request): BinaryFileResponse
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
            Route::get('export', BookingExportController::class)->name('export');

            Route::get('import', [BookingImportController::class, 'create'])->name('import.create');
            Route::post('import', [BookingImportController::class, 'store'])->name('import.store');

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

### Phase 1: Critical Fixes (P0) - 1-2 days ✅ COMPLETE
1. ✅ Remove duplicate `bookings.edit` route
2. ✅ Delete `AdminController` and update extending classes
3. ✅ Move/rename `OAuthController` to service

**Estimated Effort:** 4-6 hours
**Risk:** Low
**Breaking Changes:** None (only OAuthController rename)

---

### Phase 2: Controller Refactoring (P1) - 3-5 days

**Step 1: BookingAdminController Split** ✅ COMPLETE
1. ✅ Create `BookingExportController`
2. ✅ Create `BookingImportController`
3. ✅ Create `BookingAutoAssignController`
4. ✅ Create `BookingRouteAssignController`
5. ✅ Update routes
6. ✅ Update tests

**Step 2: EventAdminController Split** ✅ COMPLETE
1. ✅ Create `EventEmailController`
2. ✅ Move `deleteAllBookings` to `BookingAdminController` as `destroyAll`
3. ✅ Update routes
4. ✅ Update tests

**Step 3: BookingController Refactoring** ✅ COMPLETE
1. ✅ Create `SelcalValidator` (implemented as a dedicated validation Rule)
2. ✅ Create `BookingReservationController`
3. ✅ Create `BookingCancellationController`
4. ✅ Refactor `edit()` method (remove reservation logic, add timing constraints)
5. ✅ Refactor `update()` method (add timing constraints)
6. ✅ Remove `cancel()` method from `BookingController`
7. ✅ Update `BookingPolicy` with `reserve()`, `edit()`, `update()`, and `cancel()` methods
8. ✅ Update routes (reservation POST and cancellation DELETE added)
9. ✅ Update views ("Book" buttons changed to POST to reservation endpoint; cancel forms changed to DELETE cancellation endpoint)
10. ✅ Update tests

**Estimated Effort:** 16-24 hours
**Risk:** Medium (requires extensive testing)
**Breaking Changes:** Route name changes (update views)

---

### Phase 3: API Routes (P2) - 1-2 days
> **Deferred** — Tackling after Phases 4 and 5 are complete.

1. Create API v1 controllers
2. Update routes with versioning
3. Add controller tests
4. Update API documentation

**Estimated Effort:** 8-12 hours
**Risk:** Low (additive changes)
**Breaking Changes:** None if routes kept for backward compatibility

---

### Phase 4a: Low-touch Route Fixes (P2) - 0.5-1 day
Fix URL structure and HTTP method issues with minimal blast radius:
1. **#12** Airport `destroyUnused` — POST→DELETE, remove verb from URL, rename `airports.destroyUnused` → `airports.unused.destroy` (~3 references)
2. **#13** Booking admin create — remove `{bulk?}` from URL, switch to query string; fix views using `route(...) . '/bulk'` hack (~10 references)
3. **#14** Booking export — remove `{vacc?}` from URL, switch to query string (~4 references)

**Estimated Effort:** 3-5 hours
**Risk:** Low
**Breaking Changes:** Route names and URL structure change for 3 routes

---

### Phase 4b: User Settings Controller Rename (P2) - 0.5 day
1. **#15** Rename `UserController` → `UserSettingsController`
2. Rename `showSettingsForm` → `edit`, `saveSettings` → `update`
3. Switch to `Route::singleton('user/settings', UserSettingsController::class)->only(['edit', 'update'])`
4. Update views and tests (~7 references)

**Estimated Effort:** 2-3 hours
**Risk:** Low
**Breaking Changes:** Route names change (`user.settings`, `user.saveSettings`)

---

### Phase 4c: Booking Index Route Rename (P2) - 1 day ✅ COMPLETE
High blast radius but mechanical changes only:
1. ✅ **#10** Change URL from `/{event}/bookings/{filter?}` → `events/{event}/bookings`
2. ✅ Rename `bookings.event.index` → `events.bookings.index`
3. ✅ Remove unused `{filter?}` route parameter (not used in controller)
4. ✅ Update all references across controllers, views, tests, notifications, API resources, breadcrumbs

**Estimated Effort:** 3-4 hours
**Risk:** Medium (high blast radius, but purely mechanical changes)
**Breaking Changes:** Route name and URL structure change

---

### Phase 4d: FAQ Toggle → Attach/Detach (P3) - 0.5 day
1. **#17** Split `toggleEvent` into separate attach (`store`) and detach (`destroy`) endpoints
2. Create `FaqEventController` with `store` and `destroy` methods
3. Update routes: POST `faq/{faq}/events/{event}` and DELETE `faq/{faq}/events/{event}`
4. Update view and tests (~3 references)

**Estimated Effort:** 2-3 hours
**Risk:** Low
**Breaking Changes:** Route name and HTTP method change

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
- ✅ `app/Http/Controllers/Booking/BookingExportController.php`
- ✅ `app/Http/Controllers/Booking/BookingImportController.php`
- ✅ `app/Http/Controllers/Booking/BookingAutoAssignController.php`
- ✅ `app/Http/Controllers/Booking/BookingRouteAssignController.php`
- ✅ `app/Http/Controllers/Booking/BookingReservationController.php`
- ✅ `app/Http/Controllers/Booking/BookingCancellationController.php`
- ✅ `app/Http/Controllers/Event/EventEmailController.php`
- ✅ `app/Rules/ValidSelcal.php` (implemented as a validation Rule instead of a Service)
- ✅ `app/Services/OAuth/VatsimProvider.php` (renamed from OAuthController)

### Phase 3
- `app/Http/Controllers/Api/V1/EventController.php`
- `app/Http/Controllers/Api/V1/BookingController.php`
- `app/Http/Controllers/Api/V1/AirportController.php`

### Phase 4
- `app/Http/Controllers/User/UserSettingsController.php` (renamed from UserController)
- `app/Http/Controllers/Faq/FaqEventController.php` (optional)

---

## Files to Delete

- ✅ `app/Http/Controllers/AdminController.php`
- ✅ `app/Http/Controllers/OAuthController.php` (moved to services)
- `app/Http/Controllers/User/UserController.php` (renamed — Phase 4)

---

## Files to Modify

### Routes
- `routes/web.php` - Major refactoring in all phases
- `routes/api.php` - Phase 3

### Controllers (Simplified)
- ✅ `app/Http/Controllers/Booking/BookingAdminController.php` - Export/import/auto-assign/route-assign extracted; `destroyAll` added
- ✅ `app/Http/Controllers/Booking/BookingController.php` - `edit()` refactored, `cancel()` and `validateSELCAL()` removed
- ✅ `app/Http/Controllers/Event/EventAdminController.php` - Email methods and `deleteAllBookings` removed (now 113 lines, pure CRUD)
- ✅ All controllers extending `AdminController` - Now extend `Controller` directly

### Policies
- `app/Policies/BookingPolicy.php` - Add `reserve()`, `cancel()` methods, update `edit()` and `update()` with timing constraints

### Middleware
- `bootstrap/app.php` - Update middleware aliases (Phase 5)

### Views
- ✅ Updated all views using `route('bookings.cancel')` to use `route('bookings.cancellation.destroy')`
- ✅ Updated all "Book" buttons to POST to `route('bookings.reservation.store')`
- ✅ Updated `event/admin/overview.blade.php` email link to `route('admin.events.emails.bulk.create')` and delete-bookings form to `route('admin.events.bookings.destroyAll')`
- ✅ Updated `event/admin/sendEmail.blade.php` route names and changed PATCH → POST for both email forms

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
1. ✅ Start with Phase 1 (low risk, immediate value)
2. ✅ Complete Phase 2 in increments (highest value)
3. Complete Phase 4 in sub-phases (4a → 4b → 4c → 4d), lowest risk first
4. Complete Phase 5 (polish)
5. Complete Phase 3 (API routes) last — additive, no breaking changes

**Key Benefits:**
- Improved code maintainability
- Better testability
- RESTful consistency
- Clearer API versioning
- Easier onboarding for new developers
