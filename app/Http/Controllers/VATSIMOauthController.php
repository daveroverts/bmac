<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use League\OAuth2\Client\Token;
use Illuminate\Support\Facades\Auth;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

class VatsimOAuthController extends GenericProvider
{
    /**
     * @var GenericProvider
     */
    private $provider;

    /**
     * Initializes the provider variable.
     */
    public function __construct()
    {
        parent::__construct([
            'clientId'                => config('vatsim_auth.id'),    // The client ID assigned to you by the provider
            'clientSecret'            => config('vatsim_auth.secret'),   // The client password assigned to you by the provider
            'redirectUri'             => route('login'),
            'urlAuthorize'            => config('vatsim_auth.base').'/oauth/authorize',
            'urlAccessToken'          => config('vatsim_auth.base').'/oauth/token',
            'urlResourceOwnerDetails' => config('vatsim_auth.base').'/api/user',
            'scopes'                  => config('vatsim_auth.scopes'),
            'scopeSeparator'          => ' '
        ]);
    }

    /**
     * Gets an (updated) user token
     * @param Token $token
     * @return Token
     * @return null
     */
    public static function updateToken($token)
    {
        $controller = new VatsimOAuthController;
        try {
            return $controller->getAccessToken('refresh_token', [
                'refresh_token' => $token->getRefreshToken()
            ]);
        } catch (IdentityProviderException $e) {
            return null;
        }
    }
}
