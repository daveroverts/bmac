<?php

namespace Tests\Unit;

use App\Models\User;
use League\OAuth2\Client\Token\AccessToken;
use Tests\TestCase;

it('returns null from refreshTokenIfExpired when no access_token is set', function (): void {
    $user = User::factory()->create([
        'access_token' => null,
        'refresh_token' => null,
        'token_expires' => null,
    ]);

    expect($user->refreshTokenIfExpired())->toBeNull();
});

it('returns the existing token when it has not expired', function (): void {
    /** @var TestCase $this */
    $user = User::factory()->create([
        'access_token' => 'valid-access-token',
        'refresh_token' => 'valid-refresh-token',
        'token_expires' => time() + 3600,
    ]);

    $token = $user->refreshTokenIfExpired();

    expect($token)->toBeInstanceOf(AccessToken::class);
    expect($token->getToken())->toBe('valid-access-token');

    // Confirm no DB update occurred for a non-expired token
    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'access_token' => 'valid-access-token',
    ]);
});
