<?php

namespace SocialiteProviders\VatsimConnect;

use SocialiteProviders\Manager\OAuth2\AbstractProvider;
use SocialiteProviders\Manager\OAuth2\User;

class Provider extends AbstractProvider
{
    /**
     * Unique Provider Identifier.
     */
    const IDENTIFIER = 'VATSIMCONNECT';

    /**
     * {@inheritdoc}
     */
    protected $scopes = [
        'full_name',
        'email',
    ];

    /**
     * Get the host URL.
     *
     * @return string
     */
    protected function getVatsimConnectUrl()
    {
        return config('services.vatsimconnect.host');
    }

    /**
     * {@inheritdoc}
     */
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase($this->getVatsimConnectUrl() . '/oauth/authorize', $state);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl()
    {
        return $this->getVatsimConnectUrl() . '/oauth/token';
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get($this->getVatsimConnectUrl() . '/api/user', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ],
        ]);

        return json_decode($response->getBody(), true);
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user)
    {
        return (new User())->setRaw($user)->map([
            'id'        => $user['cid'],
            'name_first' => $user['personal']['name_first'] ?? null,
            'name_last' => $user['personal']['name_last'] ?? null,
            'email'     => $user['personal']['email'] ?? null,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenFields($code)
    {
        return array_merge(parent::getTokenFields($code), [
            'grant_type' => 'authorization_code'
        ]);
    }
}
