<?php

use App\Livewire\Booking\Admin\Import;
use App\Models\Event;
use App\Models\User;
use Tests\TestCase;

it('shows the booking import page with the import component', function (): void {
    /** @var TestCase $this */

    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    /** @var Event $event */
    $event = Event::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.events.bookings.import.create', $event))
        ->assertOk()
        ->assertSeeLivewire(Import::class);
});

it('does not allow non-admins to view the import page', function (): void {
    /** @var TestCase $this */

    /** @var User $user */
    $user = User::factory()->create();

    /** @var Event $event */
    $event = Event::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.events.bookings.import.create', $event))
        ->assertForbidden();
});
