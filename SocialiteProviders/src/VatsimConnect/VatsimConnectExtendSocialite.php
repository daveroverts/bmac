<?php

namespace SocialiteProviders\VatsimConnect;

use SocialiteProviders\Manager\SocialiteWasCalled;

class VatsimConnectExtendSocialite
{
    /**
     * Register the provider.
     *
     * @param  \SocialiteProviders\Manager\SocialiteWasCalled $socialiteWasCalled
     */
    public function handle(SocialiteWasCalled $socialiteWasCalled)
    {
        $socialiteWasCalled->extendSocialite('vatsimconnect', Provider::class);
    }
}
