<?php

use App\Models\User;
use Tests\TestCase;

it('redirects guests to OAuth provider', function (): void {
    /** @var TestCase $this */
    $this->get(route('login'))
        ->assertRedirect();
});

it('redirects authenticated users away from login page', function (): void {
    /** @var TestCase $this */

    /** @var User $user */
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('login'))
        ->assertRedirect('/');
});

it('allows authenticated users to logout', function (): void {
    /** @var TestCase $this */

    /** @var User $user */
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('logout'))
        ->assertRedirect('/');

    $this->assertGuest();
});

it('redirects guests to home when accessing logout endpoint', function (): void {
    /** @var TestCase $this */

    $this->post(route('logout'))
        ->assertRedirect('/');
});
