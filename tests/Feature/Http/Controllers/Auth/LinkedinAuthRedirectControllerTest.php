<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;

use function Pest\Laravel\get;

uses(RefreshDatabase::class);

it('redirects to linkedin oauth', function () {
    Socialite::fake('linkedin-openid');

    get(route('auth.login.linkedin.redirect'))
        ->assertRedirect();
});

it('redirects to linkedin oauth when auto fetch profile url is disabled', function () {
    config()->set('services.linkedin-openid.auto_fetch_profile_url', false);

    Socialite::fake('linkedin-openid');

    get(route('auth.login.linkedin.redirect'))
        ->assertRedirect();
});

it('preserves the intended url in session for callback redirect', function () {
    Socialite::fake('linkedin-openid');

    session()->put('url.intended', '/test');

    get(route('auth.login.linkedin.redirect'))
        ->assertRedirect()
        ->assertSessionHas('url.intended', '/test');
});
