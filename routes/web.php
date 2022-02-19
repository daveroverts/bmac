<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\Faq\FaqController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Event\EventController;
use App\Http\Controllers\Booking\BookingController;
use App\Http\Controllers\Event\EventAdminController;
use App\Http\Controllers\Booking\BookingAdminController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', HomeController::class)->name('home');

Route::get('/login', [LoginController::class, 'login'])->name('login');
Route::get('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('admin/login', function () {
    // Just redirect to login, we don't work with passwords
    return to_route('login');
})->name('filament.auth.login');

// Admin routes
Route::group(['as' => 'admin.', 'prefix' => 'admin', 'middleware' => 'auth.isAdmin'], function () {

    // Event
    Route::delete('events/{event}/delete-bookings', [EventAdminController::class, 'deleteAllBookings'])->name('events.delete-bookings');
    Route::get('{event}/email', [EventAdminController::class, 'sendEmailForm'])->name('events.email.form');
    Route::patch('{event}/email', [EventAdminController::class, 'sendEmail'])->name('events.email');
    Route::patch(
        '{event}/email_final',
        [EventAdminController::class, 'sendFinalInformationMail']
    )->name('events.email.final');

    // Booking
    Route::resource('bookings', BookingAdminController::class)->except(['index', 'create', 'show']);
    Route::get('{event}/bookings/export/{vacc?}', [BookingAdminController::class, 'export'])->name('bookings.export');
    Route::get('{event}/bookings/create/{bulk?}', [BookingAdminController::class, 'create'])->name('bookings.create');
    Route::get('{event}/bookings/import', [BookingAdminController::class, 'importForm'])->name('bookings.importForm');
    Route::post('{event}/bookings/import', [BookingAdminController::class, 'import'])->name('bookings.import');
    Route::get(
        '{event}/bookings/auto-assign',
        [BookingAdminController::class, 'adminAutoAssignForm']
    )->name('bookings.autoAssignForm');
    Route::post(
        '{event}/bookings/auto-assign',
        [BookingAdminController::class, 'adminAutoAssign']
    )->name('bookings.autoAssign');
    Route::get(
        '{event}/bookings/route-assign',
        [BookingAdminController::class, 'routeAssignForm']
    )->name('bookings.routeAssignForm');
    Route::post(
        '{event}/bookings/route-assign',
        [BookingAdminController::class, 'routeAssign']
    )->name('bookings.routeAssign');
});

Route::resource('bookings', BookingController::class)->only(['show', 'edit', 'update']);
Route::get('/{event}/bookings/{filter?}', [BookingController::class, 'index'])->name('bookings.event.index');
Route::get('/bookings/{booking}/edit', [BookingController::class, 'edit'])
    ->middleware('auth.isLoggedIn')->name('bookings.edit');
Route::patch('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');

Route::get('faq', FaqController::class)->name('faq');

Route::get('{event}', EventController::class)->name('events.show');

Route::middleware('auth.isLoggedIn')->group(function () {
    Route::prefix('user')->name('user.')
        ->group(function () {
            Route::get('settings', [UserController::class, 'showSettingsForm'])->name('settings');
            Route::patch('settings', [UserController::class, 'saveSettings'])->name('saveSettings');
        });
});
