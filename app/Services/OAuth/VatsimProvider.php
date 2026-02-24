<?php

namespace App\Services\OAuth;

use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessTokenInterface;

class VatsimProvider extends GenericProvider
{
    /**
     * Initializes the provider variable.
     */
    public function __construct()
    {
        parent::__construct([
            'clientId'                => config('oauth.id'),    // The client ID assigned to you by the provider
            'clientSecret'            => config('oauth.secret'),   // The client password assigned to you by the provider
            'redirectUri'             => route('login'),
            'urlAuthorize'            => config('oauth.base') . '/oauth/authorize',
            'urlAccessToken'          => config('oauth.base') . '/oauth/token',
            'urlResourceOwnerDetails' => config('oauth.base') . '/api/user',
            'scopes'                  => config('oauth.scopes'),
            'scopeSeparator'          => ' '
        ]);
    }

    public static function updateToken($token): ?AccessTokenInterface
    {
        $controller = new VatsimProvider();
        try {
            return $controller->getAccessToken('refresh_token', [
                'refresh_token' => $token->getRefreshToken()
            ]);
        } catch (IdentityProviderException) {
            return null;
        }
    }

    public static function getOAuthProperty(string $property, mixed $data): mixed
    {
        return data_get($data, str_replace('-', '.', $property)) ?: false;
    }
}
