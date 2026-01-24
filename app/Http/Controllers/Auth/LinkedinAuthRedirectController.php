<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Socialite;
use Laravel\Socialite\Two\LinkedInOpenIdProvider;
use Symfony\Component\HttpFoundation\RedirectResponse;

class LinkedinAuthRedirectController extends BaseController
{
    public function __invoke(): RedirectResponse
    {
        /** @var LinkedInOpenIdProvider */
        $driver = Socialite::driver('linkedin-openid');

        $this->preserveIntendedUrl();
        $this->addScopesIfRequired(driver: $driver);

        return $driver->redirect();
    }

    private function preserveIntendedUrl(): void
    {
        if ($intended = Session::get('url.intended')) {
            Session::put('url.intended', $intended);

            return;
        }

        $previous = url()->previous();

        if (Str::of($previous)->startsWith(route('login')) === false) {
            Session::put('url.intended', $previous);
        }
    }

    private function addScopesIfRequired(Provider $driver): void
    {
        if (Config::get('services.linkedin-openid.auto_fetch_profile_url') === true && $driver instanceof LinkedInOpenIdProvider) {
            $driver->scopes(['r_profile_basicinfo']);
        }
    }
}
