<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\VotingController;
use App\Http\Controllers\Faq\FaqController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Event\EventController;
use App\Http\Controllers\Faq\FaqAdminController;
use App\Http\Controllers\Booking\BookingController;
use App\Http\Controllers\Event\EventAdminController;
use App\Http\Controllers\Airport\AirportAdminController;
use App\Http\Controllers\Booking\BookingAdminController;
use App\Http\Controllers\AirportLink\AirportLinkAdminController;
use App\Http\Controllers\EventLink\EventLinkAdminController;

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
Route::get('/node', function () {
    return 'I am Node 1.';
});
Route::get('/login', [LoginController::class, 'login'])->name('login');
Route::get('/logout', [LoginController::class, 'logout'])->name('logout');

// Admin routes
Route::group(['as' => 'admin.', 'prefix' => 'admin', 'middleware' => 'auth.isAdmin'], function () {
    // Airports
    Route::post('airports/destroy-unused', [AirportAdminController::class, 'destroyUnused'])->name('airports.destroyUnused');
    Route::resource('airports', AirportAdminController::class);

    // AirportLinks
    Route::resource('airportLinks', AirportLinkAdminController::class)->except(['show']);

    // EventLinks
    Route::resource('eventLinks', EventLinkAdminController::class)->except(['show']);

    // Faq
    Route::resource('faq', FaqAdminController::class)->except('show');
    Route::patch('faq/{faq}/toggle-event/{event}', [FaqAdminController::class, 'toggleEvent'])->name('faq.toggleEvent');

    // Event
    Route::delete('events/{event}/delete-bookings', [EventAdminController::class, 'deleteAllBookings'])->name('events.delete-bookings');
    Route::resource('events', EventAdminController::class);
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
    Route::post("{event}/bookings/unconfirmremove",[BookingAdminController::class, 'unConfirmRemove'])->name('bookings.inshallah');

    //Voting ones
    Route::get('voting',[VotingController::class, 'showAdminPage'])->name("voting");
    Route::get('voting/addnew',[VotingController::class, 'showAddPage'])->name("voting.addnew");
    Route::post('voting/addpoll',[VotingController::class, 'addNewPoll'])->name('voting.addpoll');
    Route::get('voting/viewpoll/{poll_id}',[VotingController::class, 'viewPoll'])->name('voting.viewpoll');
    Route::post('voting/editpoll',[VotingController::class, 'editPoll'])->name('voting.editpoll');
});

Route::resource('bookings', BookingController::class)->only(['show', 'edit', 'update']);
Route::get('/{event}/bookings/{filter?}', [BookingController::class, 'index'])->name('bookings.event.index');
Route::get('/bookings/{booking}/edit', [BookingController::class, 'edit'])
    ->middleware('auth.isLoggedIn')->name('bookings.edit');

Route::post('/bookings/{booking}/confirm', [BookingController::class, 'confirm'])->middleware('auth.isLoggedIn')->name('bookings.confirm');


Route::patch('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');

Route::get('faq', FaqController::class)->name('faq');
Route::get('voting',[VotingController::class, 'pubVotingPage'])->middleware('auth.isLoggedIn')->name('voting.main');
Route::post('voting/cast_vote',[VotingController::class,'pubVote'])->middleware('auth.isLoggedIn')->name('voting.vote');

Route::get('{event}', EventController::class)->name('events.show');

Route::middleware('auth.isLoggedIn')->group(function () {
    Route::prefix('user')->name('user.')
        ->group(function () {
            Route::get('settings', [UserController::class, 'showSettingsForm'])->name('settings');
            Route::patch('settings', [UserController::class, 'saveSettings'])->name('saveSettings');
        });

});
