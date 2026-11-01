<?php

use App\Livewire\Event\Admin\Overview;
use App\Models\Event;
use App\Models\User;
use Livewire\Livewire;

it('returns 404 for non-admin users', function (): void {
    /** @var User $user */
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Overview::class)
        ->assertStatus(404);
});

it('lists events for admin users', function (): void {
    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    $event = Event::factory()->create(['name' => 'Amsterdam to London']);

    Livewire::actingAs($admin)
        ->test(Overview::class)
        ->assertSee('Amsterdam to London')
        ->assertSee(route('admin.events.edit', $event));
});

it('filters events by name', function (): void {
    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    Event::factory()->create(['name' => 'Amsterdam to London']);
    Event::factory()->create(['name' => 'Paris Fly-In']);

    Livewire::actingAs($admin)
        ->test(Overview::class)
        ->set('search', 'London')
        ->assertSee('Amsterdam to London')
        ->assertDontSee('Paris Fly-In');
});

it('filters events by upcoming and past period', function (): void {
    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    Event::factory()->create(['name' => 'Upcoming Event']);
    Event::factory()->expired()->create(['name' => 'Past Event']);

    Livewire::actingAs($admin)
        ->test(Overview::class)
        ->set('period', 'upcoming')
        ->assertSee('Upcoming Event')
        ->assertDontSee('Past Event')
        ->set('period', 'past')
        ->assertSee('Past Event')
        ->assertDontSee('Upcoming Event')
        ->set('period', 'all')
        ->assertSee('Upcoming Event')
        ->assertSee('Past Event');
});

it('shows an empty state when no events match', function (): void {
    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(Overview::class)
        ->set('search', 'nothing matches this')
        ->assertSee('No events found.');
});

it('resets pagination when the search changes', function (): void {
    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    Event::factory()->count(20)->create();

    Livewire::actingAs($admin)
        ->test(Overview::class)
        ->call('setPage', 2)
        ->set('search', 'a')
        ->assertSet('paginators.page', 1);
});

it('treats an unknown period as all', function (): void {
    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    Event::factory()->create(['name' => 'Upcoming Event']);
    Event::factory()->expired()->create(['name' => 'Past Event']);

    Livewire::actingAs($admin)
        ->test(Overview::class)
        ->set('period', 'bogus')
        ->assertSee('Upcoming Event')
        ->assertSee('Past Event');
});

it('resets pagination when the period changes', function (): void {
    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    Event::factory()->count(20)->create();

    Livewire::actingAs($admin)
        ->test(Overview::class)
        ->call('setPage', 2)
        ->set('period', 'upcoming')
        ->assertSet('paginators.page', 1);
});

it('renders a Bootstrap pager', function (): void {
    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    Event::factory()->count(20)->create();

    Livewire::actingAs($admin)
        ->test(Overview::class)
        ->assertSeeHtml('class="pagination"');
});

it('hides import data for events that have ended', function (): void {
    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    $upcoming = Event::factory()->create();
    $past = Event::factory()->expired()->create();

    Livewire::actingAs($admin)
        ->test(Overview::class)
        ->assertSeeHtml('id="delete-event-' . $upcoming->id . '"')
        ->assertSeeHtml('id="delete-event-' . $past->id . '"')
        ->assertSee(route('admin.events.bookings.import.create', $upcoming))
        ->assertDontSee(route('admin.events.bookings.import.create', $past));
});

it('renders the period filter as a radio button group with the active period checked', function (): void {
    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    $html = Livewire::actingAs($admin)
        ->test(Overview::class)
        ->set('period', 'past')
        ->html();

    expect($html)->toContain('class="btn-group"')
        ->toContain('id="period-all"')
        ->toContain('id="period-upcoming"')
        ->toMatch('/id="period-past"[^>]*checked/');
});
