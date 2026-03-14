<?php

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Event;
use App\Models\Flight;
use App\Models\User;
use App\Services\OAuth\VatsimProvider;
use League\OAuth2\Client\Provider\GenericResourceOwner;
use League\OAuth2\Client\Token\AccessToken;
use Tests\TestCase;

/**
 * Builds a stub VatsimProvider that impersonates the given user.
 */
function fakeProviderFor(User $user): VatsimProvider
{
    $token = new AccessToken([
        'access_token' => 'fake-token',
        'expires' => now()->addHour()->timestamp,
    ]);

    $resourceOwner = new GenericResourceOwner([
        'data' => [
            'cid' => $user->id,
            'personal' => [
                'name_first' => $user->name_first,
                'name_last' => $user->name_last,
                'email' => $user->email,
            ],
        ],
    ], 'data');

    /** @var VatsimProvider&\Mockery\MockInterface $provider */
    $provider = Mockery::mock(VatsimProvider::class);
    $provider->allows('getAccessToken')->andReturn($token);
    $provider->allows('getResourceOwner')->andReturn($resourceOwner);
    $provider->allows('getOAuthProperty')->andReturnUsing(
        fn (string $property, mixed $data): mixed => (new VatsimProvider())->getOAuthProperty($property, $data)
    );

    return $provider;
}

it('reserves an unassigned booking after login and redirects to bookings.edit', function (): void {
    /** @var TestCase $this */

    auth()->logout();

    /** @var User $user */
    $user = User::factory()->create();

    /** @var Event $event */
    $event = Event::factory()->create([
        'startBooking' => now()->subDay(),
        'endBooking' => now()->addDay(),
    ]);

    /** @var Flight $flight */
    $flight = Flight::factory()->create([
        'booking_id' => Booking::factory()->create([
            'event_id' => $event->id,
            'status' => BookingStatus::UNASSIGNED,
        ])->id,
    ]);

    $this->instance(VatsimProvider::class, fakeProviderFor($user));

    $this->withSession(['oauthstate' => 'test-state', 'booking' => $flight->booking->uuid])
        ->get(route('login', ['code' => 'fake-code', 'state' => 'test-state']))
        ->assertRedirect(route('bookings.edit', $flight->booking));

    $flight->booking->refresh();
    expect($flight->booking->status)->toBe(BookingStatus::RESERVED);
    expect($flight->booking->user_id)->toBe($user->id);
});

it('redirects to events bookings index when booking window is closed after login', function (): void {
    /** @var TestCase $this */

    auth()->logout();

    /** @var User $user */
    $user = User::factory()->create();

    /** @var Event $event */
    $event = Event::factory()->create([
        'startBooking' => now()->subDays(2),
        'endBooking' => now()->subDay(),
    ]);

    /** @var Flight $flight */
    $flight = Flight::factory()->create([
        'booking_id' => Booking::factory()->create([
            'event_id' => $event->id,
            'status' => BookingStatus::UNASSIGNED,
        ])->id,
    ]);

    $this->instance(VatsimProvider::class, fakeProviderFor($user));

    $this->withSession(['oauthstate' => 'test-state', 'booking' => $flight->booking->uuid])
        ->get(route('login', ['code' => 'fake-code', 'state' => 'test-state']))
        ->assertRedirect(route('events.bookings.index', $event));

    $flight->booking->refresh();
    expect($flight->booking->status)->toBe(BookingStatus::UNASSIGNED);
    expect($flight->booking->user_id)->toBeNull();
});

it('redirects to bookings.show when session booking is already booked after login', function (): void {
    /** @var TestCase $this */

    auth()->logout();

    /** @var User $user */
    $user = User::factory()->create();

    /** @var Flight $flight */
    $flight = Flight::factory()->create([
        'booking_id' => Booking::factory()->booked()->create([
            'user_id' => $user->id,
        ])->id,
    ]);

    $this->instance(VatsimProvider::class, fakeProviderFor($user));

    $this->withSession(['oauthstate' => 'test-state', 'booking' => $flight->booking->uuid])
        ->get(route('login', ['code' => 'fake-code', 'state' => 'test-state']))
        ->assertRedirect(route('bookings.show', $flight->booking));
});
