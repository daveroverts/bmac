<?php

use App\Models\User;
use App\Models\Faq;
use Tests\TestCase;

it('prevents non-admin users from accessing FAQ admin index', function (): void {
    /** @var TestCase $this */

    /** @var User $user */
    $user = User::factory()->create();

    $this->actingAs($user)
        ->from('/')
        ->get(route('admin.faq.index'))
        ->assertRedirect('/');
});

it('allows admin users to view FAQ index', function (): void {
    /** @var TestCase $this */

    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.faq.index'))
        ->assertOk();
});

it('allows admin users to view create FAQ form', function (): void {
    /** @var TestCase $this */

    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.faq.create'))
        ->assertOk();
});

it('allows admin users to create FAQs', function (): void {
    /** @var TestCase $this */

    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('admin.faq.store'), [
            'is_online' => true,
            'question' => 'Test Question',
            'answer' => 'Test Answer',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('faqs', [
        'question' => 'Test Question',
        'answer' => 'Test Answer',
    ]);
});

it('allows admin users to view edit FAQ form', function (): void {
    /** @var TestCase $this */

    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    /** @var Faq $faq */
    $faq = Faq::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.faq.edit', $faq))
        ->assertOk();
});

it('allows admin users to update FAQs', function (): void {
    /** @var TestCase $this */

    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    /** @var Faq $faq */
    $faq = Faq::factory()->create([
        'question' => 'Original Question',
    ]);

    $this->actingAs($admin)
        ->patch(route('admin.faq.update', $faq), [
            'is_online' => true,
            'question' => 'Updated Question',
            'answer' => $faq->answer,
        ])
        ->assertRedirect();

    $faq->refresh();
    expect($faq->question)->toBe('Updated Question');
});

it('allows admin users to delete FAQs', function (): void {
    /** @var TestCase $this */

    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    /** @var Faq $faq */
    $faq = Faq::factory()->create();

    $this->actingAs($admin)
        ->delete(route('admin.faq.destroy', $faq))
        ->assertRedirect(route('admin.faq.index'));

    $this->assertDatabaseMissing('faqs', [
        'id' => $faq->id,
    ]);
});
