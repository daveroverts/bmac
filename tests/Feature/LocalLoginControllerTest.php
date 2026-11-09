<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertAuthenticatedAs;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertGuest;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\withSession;

beforeEach(function (): void {
    auth()->logout();
    app()->instance('env', 'local');
    // The test app boots as "testing", so the local-only routes are registered here by hand.
    Route::middleware('web')->group(base_path('routes/local.php'));
    resolve('router')->getRoutes()->refreshNameLookups();
    withSession(['_token' => 'local-login-test-token']);
});

it('logs in the dedicated local account for each role and renews the session', function (string $role, int $id, bool $isAdmin): void {
    withSession(['retained' => 'value']);
    $sessionId = session()->getId();
    $token = session()->token();

    post(route('login.local', $role), ['_token' => session()->token()])
        ->assertRedirectToRoute('home')
        ->assertSessionHas('retained', 'value');

    $user = User::query()->findOrFail($id);
    assertAuthenticatedAs($user);
    expect($user->isAdmin)->toBe($isAdmin);
    expect(session()->getId())->not->toBe($sessionId);
    expect(session()->token())->not->toBe($token);
})->with(['admin' => ['admin', 10000001, true], 'regular user' => ['user', 10000002, false]]);

it('never logs in as another existing account, such as a real member in a copied database', function (string $role, bool $isAdmin): void {
    User::query()->delete();
    $member = User::factory()->create(['id' => 1234567, 'isAdmin' => $isAdmin]);
    $attributes = $member->fresh()->getAttributes();

    post(route('login.local', $role), ['_token' => session()->token()])->assertRedirectToRoute('home');

    expect(auth()->id())->not->toBe($member->id);
    expect($member->fresh()->getAttributes())->toBe($attributes);
    assertDatabaseCount('users', 2);
})->with(['admin' => ['admin', true], 'regular user' => ['user', false]]);

it('creates both local accounts once and switches between them', function (): void {
    User::query()->delete();

    post(route('login.local', 'admin'), ['_token' => session()->token()])->assertRedirectToRoute('home');
    $admin = User::query()->findOrFail(10000001);
    expect($admin)->name_first->toBe('Local')->name_last->toBe('Admin')
        ->email->toBe('local-admin-10000001@example.test')->isAdmin->toBeTrue();

    post(route('login.local', 'user'), ['_token' => session()->token()])->assertRedirectToRoute('home');
    $user = User::query()->findOrFail(10000002);
    assertAuthenticatedAs($user);
    expect($user)->name_first->toBe('Local')->name_last->toBe('Pilot')
        ->email->toBe('local-pilot-10000002@example.test')->isAdmin->toBeFalse();

    post(route('login.local', 'admin'), ['_token' => session()->token()])->assertRedirectToRoute('home');
    assertAuthenticatedAs($admin);
    assertDatabaseCount('users', 2);
});

it('restores the role of an existing local account', function (): void {
    User::factory()->create(['id' => 10000001, 'isAdmin' => false, 'name_first' => 'Kept']);
    actingAs(User::factory()->create());

    post(route('login.local', 'admin'), ['_token' => session()->token()])->assertRedirectToRoute('home');

    $admin = User::query()->findOrFail(10000001);
    assertAuthenticatedAs($admin);
    expect($admin)->isAdmin->toBeTrue()->name_first->toBe('Kept');
});

it('hides the dropdown and blocks login outside local even with registered routes', function (string $environment): void {
    User::query()->delete();
    app()->instance('env', $environment);
    get(route('home'))->assertOk()->assertDontSee('Local login');

    post(route('login.local', 'admin'), ['_token' => session()->token()])->assertNotFound();
    assertGuest();
    assertDatabaseCount('users', 0);
})->with(['production', 'staging', 'testing']);

it('does not register the local login route outside local', function (): void {
    $this->refreshApplication();

    expect(Route::has('login.local'))->toBeFalse();
    $this->post('/local-login/admin')->assertNotFound();
    $this->assertGuest();
});

it('rejects invalid roles without authenticating', function (): void {
    post('/local-login/owner', ['_token' => session()->token()])->assertNotFound();
    assertGuest();
});

it('shows local login to guests and signed in users while preserving OAuth login', function (): void {
    get(route('home'))->assertOk()
        ->assertSee('Local login')->assertSee('Regular user')
        ->assertSee(route('login'))->assertSeeHtml('name="_token"');

    actingAs(User::factory()->create());
    get(route('home'))->assertOk()->assertSee('Local login')->assertSee('Regular user');
});

it('does not allow GET requests to log in', function (): void {
    get(route('login.local', 'admin'))->assertMethodNotAllowed();
    assertGuest();
});
