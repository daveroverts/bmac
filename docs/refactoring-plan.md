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

### 12. Airport "Destroy Unused" Route ✅ COMPLETE

**Location:** `routes/web.php:43`

**Previous State:**
```php
Route::post('airports/destroy-unused', [AirportAdminController::class, 'destroyUnused'])
    ->name('airports.destroyUnused');
```

**Implemented State:**
```php
Route::delete('airports/unused', [AirportAdminController::class, 'destroyUnused'])
    ->name('airports.unused.destroy');
```

HTTP method changed from POST to DELETE, verb removed from URL, route name standardized.

**Impact:** Low - consistency

---

### 13. Booking Admin Create Route with Bulk Parameter ✅ COMPLETE

**Location:** `routes/web.php:67`

**Previous State:**
```php
Route::get('{event}/bookings/create', [BookingAdminController::class, 'create'])
    ->name('bookings.create');
```

**Implemented State:**
```php
Route::get('events/{event}/bookings/create', [BookingAdminController::class, 'create'])
    ->name('events.bookings.create');
```

URL prefix updated to `events/{event}`, route name updated to `events.bookings.create`. All 7 event-scoped booking admin routes updated consistently. Views, breadcrumbs, and tests updated.

**Impact:** Low - consistency

---

### 14. Booking Export Route with Optional Parameter ✅ COMPLETE

**Location:** `routes/web.php:66`

**Previous State:**
```php
Route::get('{event}/bookings/export/{vacc?}', [BookingAdminController::class, 'export'])
    ->name('bookings.export');
```

**Implemented State:**
```php
Route::get('events/{event}/bookings/export', BookingExportController::class)
    ->name('events.bookings.export');
```

Export extracted to `BookingExportController`, `{vacc?}` URL parameter converted to query string, URL updated to use `events/{event}` prefix, route name standardized.

**Impact:** Low - consistency

---

### 15. User Settings Routes ✅ COMPLETE

**Location:** `routes/web.php:91-93`

**Previous State:**
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

**Implemented State:**
```php
Route::middleware('auth.isLoggedIn')->prefix('user')->name('user.')->group(function (): void {
    Route::singleton('settings', UserSettingsController::class)->only(['edit', 'update']);
});
```

`UserController` renamed to `UserSettingsController`, methods renamed to `edit` and `update`, route changed to singleton resource.

**Impact:** Medium - RESTful consistency

---

## P3: Naming & Convention Inconsistencies

### 16. Route Name Inconsistencies

**Previously Fixed:**

| Old Name | New Name | Status |
|----------|----------|--------|
| `airports.destroyUnused` | `airports.unused.destroy` | ✅ Done |
| `faq.toggleEvent` | `faq.events.attach` / `faq.events.detach` | ✅ Done |
| `bookings.export` | `events.bookings.export` | ✅ Done |
| `bookings.autoAssignForm` | `bookings.autoAssign.create` | ✅ camelCase/Form suffix fixed |
| `bookings.autoAssign` | `bookings.autoAssign.store` | ✅ Form suffix fixed |
| `bookings.routeAssignForm` | `bookings.routeAssign.create` | ✅ camelCase/Form suffix fixed |
| `bookings.routeAssign` | `bookings.routeAssign.store` | ✅ Form suffix fixed |

**All Fixed:**

| Old Name | New Name | Status |
|----------|----------|--------|
| `bookings.create` | `events.bookings.create` | ✅ Done |
| `bookings.import.create` | `events.bookings.import.create` | ✅ Done |
| `bookings.import.store` | `events.bookings.import.store` | ✅ Done |
| `bookings.autoAssign.create` | `events.bookings.autoAssign.create` | ✅ Done |
| `bookings.autoAssign.store` | `events.bookings.autoAssign.store` | ✅ Done |
| `bookings.routeAssign.create` | `events.bookings.routeAssign.create` | ✅ Done |
| `bookings.routeAssign.store` | `events.bookings.routeAssign.store` | ✅ Done |

URLs also updated from `{event}/bookings/...` to `events/{event}/bookings/...` for all 7 routes.

**Impact:** Low - consistency and predictability

---

### 17. FAQ Toggle Event Route ✅ COMPLETE

Implemented as Phase 4d. See that section for details.

**Impact:** Low - clarity

---

## P3: Middleware Consistency

### 18. Replace `IsLoggedIn` with Laravel's Built-in `auth` Middleware

**Current State:**

Custom `IsLoggedIn` middleware (`app/Http/Middleware/IsLoggedIn.php`) flashes a SweetAlert message ("You need to be logged in before you can do that") and calls `return back()`. This is used under the alias `auth.isLoggedIn` in 4 places in `routes/web.php`.

**Issue:** This reimplements what Laravel's built-in `auth` middleware already does, but with worse UX — sending the user *back* instead of to the login page means they have to find the login button themselves. Laravel's `auth` middleware redirects to the `login` named route (which triggers the VATSIM OAuth flow), and supports intended URL redirects after authentication.

**Proposed State:**
1. Delete `app/Http/Middleware/IsLoggedIn.php`
2. Remove the `auth.isLoggedIn` alias from `bootstrap/app.php`
3. Replace all `auth.isLoggedIn` references in `routes/web.php` with the built-in `auth` middleware
4. Configure `redirectGuestsTo` in `bootstrap/app.php` if the default redirect-to-`login` behavior needs customization

The flash message is not needed — being redirected to login *is* the message.

**Impact:** Low - removes custom code in favor of framework convention

---

### 18b. Replace `IsAdmin` Middleware with Gate + Built-in `auth`

**Current State:**
```php
// bootstrap/app.php
'auth.isAdmin' => IsAdmin::class,

// routes/web.php
Route::group(['middleware' => 'auth.isAdmin'], function () { ... });
```

The `IsAdmin` middleware checks both authentication *and* admin status, then silently redirects non-admins to home.

**Issue:** This bundles two concerns (auth + authorization) into one middleware. The silent redirect also makes debugging harder — a 403 is the correct HTTP response for "authenticated but not authorized."

**Proposed State:**
1. Delete `app/Http/Middleware/IsAdmin.php`
2. Remove the `auth.isAdmin` alias from `bootstrap/app.php`
3. Define an `admin` Gate in `AppServiceProvider`:
   ```php
   Gate::define('admin', fn (User $user) => $user->isAdmin);
   ```
4. Update admin route group to use `['auth', 'can:admin']`:
   ```php
   Route::middleware(['auth', 'can:admin'])->prefix('admin')->name('admin.')->group(function () {
       // ...
   });
   ```

**Benefits:**
- `auth` handles "is logged in?" (redirects to login if not)
- `can:admin` handles "is admin?" (returns 403 if not)
- The `admin` gate is reusable in Blade (`@can('admin')`) and controllers (`$this->authorize('admin')`)
- Two fewer custom middleware files to maintain

**Impact:** Low - only used in `routes/web.php`

---

### 18c. Standardize Middleware Application Style

**Current Issues:**

~~Duplicate middleware on booking edit route~~ — Fixed in Phase 1.

**Mixed middleware approaches remain in `routes/web.php`:**
- Array-based route group: `Route::group(['middleware' => 'auth.isAdmin'], ...)` (line 41)
- Per-action on resource: `->middleware(['edit' => 'auth.isLoggedIn'])` (line 78)
- Inline per-route: `->middleware('auth.isLoggedIn')` (lines 80, 83)
- Fluent group: `Route::middleware('auth.isLoggedIn')->...->group(...)` (line 91)
- Constructor-based (legacy): `$this->middleware('guest')` in `LoginController` (line 34)

**Proposed Standard:**

1. **Use fluent middleware groups for multiple related routes:**
```php
Route::middleware('auth')->group(function () {
    // Multiple routes requiring authentication
});
```

2. **Use resource middleware for conditional per-action middleware:**
```php
Route::resource('bookings', BookingController::class)
    ->only(['edit'])
    ->middleware(['edit' => 'auth']);
```

3. **Use Policy authorization in controllers for complex logic**

4. **Remove constructor-based middleware** (`$this->middleware(...)` in `LoginController`) — this is a pre-Laravel 11 pattern; move `guest` middleware to route-level in `routes/web.php`.

**Impact:** Low - consistency

---

## P3: Route Organization & Grouping

### 20. Admin Routes Could Use Better Nesting

**Current State:**
All admin routes are in a flat group with prefix `admin`.

**Proposed Enhancement:**
Group related resources for better organization:

```php
Route::middleware(['auth', 'can:admin'])->prefix('admin')->name('admin.')->group(function () {

    // Core Resources
    Route::resource('events', EventAdminController::class);
    Route::resource('airports', AirportAdminController::class);
    Route::resource('faq', FaqAdminController::class)->except('show');

    // Link Resources (could be nested under parent)
    Route::resource('airportLinks', AirportLinkAdminController::class)->except(['show']);
    Route::resource('eventLinks', EventLinkAdminController::class)->except(['show']);

    // FAQ events (attach/detach)
    Route::post('faq/{faq}/events/{event}', [FaqEventController::class, 'store'])->name('faq.events.attach');
    Route::delete('faq/{faq}/events/{event}', [FaqEventController::class, 'destroy'])->name('faq.events.detach');

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

### Phase 4a: Low-touch Route Fixes (P2) - 0.5-1 day ✅ COMPLETE
Fix URL structure and HTTP method issues with minimal blast radius:
1. ✅ **#12** Airport `destroyUnused` — POST→DELETE, remove verb from URL, rename `airports.destroyUnused` → `airports.unused.destroy`
2. ✅ **#13** Booking admin create — `{bulk?}` removed (query string), URL prefix updated to `events/{event}`, all 7 event-scoped routes renamed to `events.bookings.*`
3. ✅ **#14** Booking export — `{vacc?}` removed, query string used, extracted to `BookingExportController`, route renamed to `events.bookings.export`

**Estimated Effort:** 1-2 hours
**Risk:** Low
**Breaking Changes:** Route names changed for 7 routes

---

### Phase 4b: User Settings Controller Rename (P2) - 0.5 day ✅ COMPLETE
1. ✅ **#15** Rename `UserController` → `UserSettingsController`
2. ✅ Rename `showSettingsForm` → `edit`, `saveSettings` → `update`
3. ✅ Switch to `Route::singleton('settings', UserSettingsController::class)->only(['edit', 'update'])`
4. ✅ Update views and tests

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

### Phase 4d: FAQ Toggle → Attach/Detach (P3) - 0.5 day ✅ COMPLETE
1. ✅ **#17** Split `toggleEvent` into separate attach (`store`) and detach (`destroy`) endpoints
2. ✅ Create `FaqEventController` with `store` and `destroy` methods
3. ✅ Update routes: POST `faq/{faq}/events/{event}` and DELETE `faq/{faq}/events/{event}`
4. ✅ Update view and tests (~3 references)

**Estimated Effort:** 2-3 hours
**Risk:** Low
**Breaking Changes:** Route name and HTTP method change

---

### Phase 5: Polish (P3) - 1-2 days
1. Replace `IsLoggedIn` with Laravel's built-in `auth` middleware and delete `IsLoggedIn.php`
2. Replace `IsAdmin` middleware with an `admin` Gate + built-in `auth` middleware, delete `IsAdmin.php`
3. Remove constructor-based `$this->middleware('guest')` from `LoginController` — move to route-level
4. Standardize middleware application style (fluent groups, no array-based `Route::group`)
5. Reorganize admin route group with better nesting (section 20)
6. Final cleanup (Pint, PHPStan, Rector)

**Estimated Effort:** 4-6 hours
**Risk:** Low
**Breaking Changes:** Both custom middleware deleted (replaced by built-in `auth` + `can:admin` Gate)

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
- ✅ `app/Http/Controllers/User/UserSettingsController.php` (renamed from UserController)
- ✅ `app/Http/Controllers/Faq/FaqEventController.php`

---

## Files to Delete

- ✅ `app/Http/Controllers/AdminController.php`
- ✅ `app/Http/Controllers/OAuthController.php` (moved to services)
- ✅ `app/Http/Controllers/User/UserController.php` (renamed to `UserSettingsController` — Phase 4b)
- `app/Http/Middleware/IsLoggedIn.php` (replaced by built-in `auth` middleware — Phase 5)
- `app/Http/Middleware/IsAdmin.php` (replaced by `admin` Gate + built-in `auth` middleware — Phase 5)

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
- ✅ `app/Policies/BookingPolicy.php` - Added `reserve()`, `cancel()` methods, updated `edit()` and `update()` with timing constraints

### Middleware
- `bootstrap/app.php` - Remove both custom middleware aliases (Phase 5)
- `app/Http/Controllers/Auth/LoginController.php` - Remove constructor-based `$this->middleware('guest')` (Phase 5)
- `app/Providers/AppServiceProvider.php` - Register `admin` Gate (Phase 5)

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
- `IsLoggedIn` middleware deleted, replaced by Laravel's built-in `auth` middleware
- `IsAdmin` middleware deleted, replaced by `admin` Gate + built-in `auth` middleware (non-admins now get 403 instead of silent redirect to home)
- Constructor-based `guest` middleware moved to route-level
- Minimal impact — all middleware references are in `routes/web.php` and `bootstrap/app.php`

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
3. ✅ Phase 4: All sub-phases complete (4a, 4b, 4c, 4d)
4. Complete Phase 5 (polish)
5. Complete Phase 3 (API routes) last — additive, no breaking changes

**Key Benefits:**
- Improved code maintainability
- Better testability
- RESTful consistency
- Clearer API versioning
- Easier onboarding for new developers
