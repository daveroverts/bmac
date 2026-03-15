<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Services\OAuth\VatsimProvider;
use Illuminate\Http\Request;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

class AuthenticationService
{
    public function __construct(protected VatsimProvider $provider)
    {
    }

    /**
     * Exchange an OAuth authorization code for user data and log the user in.
     *
     * @return array{user: User, data: array{cid: mixed, first_name: mixed, last_name: mixed, email: mixed}}|null
     */
    public function authenticateFromOAuth(Request $request): ?array
    {
        try {
            $accessToken = $this->provider->getAccessToken('authorization_code', [
                'code' => $request->input('code'),
            ]);
        } catch (IdentityProviderException) {
            return null;
        }

        /** @var \League\OAuth2\Client\Token\AccessToken $accessToken */
        $resourceOwner = json_decode(json_encode($this->provider->getResourceOwner($accessToken)->toArray()));

        $data = [
            'cid' => $this->provider->getOAuthProperty(config('oauth.mapping_cid'), $resourceOwner),
            'first_name' => $this->provider->getOAuthProperty(config('oauth.mapping_first_name'), $resourceOwner),
            'last_name' => $this->provider->getOAuthProperty(config('oauth.mapping_last_name'), $resourceOwner),
            'email' => $this->provider->getOAuthProperty(config('oauth.mapping_mail'), $resourceOwner),
        ];

        if (! $data['cid'] || ! $data['first_name'] || ! $data['last_name'] || ! $data['email']) {
            return null;
        }

        $user = $this->upsertUser($data, $accessToken);

        return ['user' => $user, 'data' => $data];
    }

    /**
     * Create or update the user record and log them in.
     */
    protected function upsertUser(array $data, $token): User
    {
        $account = User::updateOrCreate(
            ['id' => $data['cid']],
            [
                'name_first' => $data['first_name'],
                'name_last' => $data['last_name'],
                'email' => $data['email'],
            ]
        );

        if ($token->getToken() !== null) {
            $account->access_token = $token->getToken();
        }

        if ($token->getRefreshToken() !== null) {
            $account->refresh_token = $token->getRefreshToken();
        }

        if ($token->getExpires() !== null) {
            $account->token_expires = $token->getExpires();
        }

        $account->save();
        auth()->loginUsingId($data['cid'], true);
        activity()->log('Login');

        return $account;
    }
}
