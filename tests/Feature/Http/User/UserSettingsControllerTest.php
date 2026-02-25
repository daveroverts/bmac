<?php

use App\Models\User;
use App\Enums\AirportView;
use Tests\TestCase;

it('allows authenticated users to view settings page', function (): void {
    /** @var TestCase $this */

    /** @var User $user */
    $user = User::factory()->create([
        'airport_view' => AirportView::NAME,
    ]);

    $this->actingAs($user)
        ->get(route('user.settings.edit'))
        ->assertOk();
});

it('allows authenticated users to update settings', function (): void {
    /** @var TestCase $this */

    /** @var User $user */
    $user = User::factory()->create([
        'airport_view' => AirportView::NAME,
        'use_monospace_font' => false,
    ]);

    $this->actingAs($user)
        ->patch(route('user.settings.update'), [
            'airport_view' => 2,
            'use_monospace_font' => true,
        ])
        ->assertRedirect();

    $user->refresh();
    expect($user->airport_view)->toBe(AirportView::IATA);
    expect($user->use_monospace_font)->toBeTrue();
});

it('validates required fields when updating settings', function (): void {
    /** @var TestCase $this */

    /** @var User $user */
    $user = User::factory()->create([
        'airport_view' => AirportView::NAME,
    ]);

    $this->actingAs($user)
        ->from(route('user.settings.edit'))
        ->patch(route('user.settings.update'), [])
        ->assertRedirect(route('user.settings.edit'))
        ->assertSessionHasErrors(['airport_view', 'use_monospace_font']);
});
