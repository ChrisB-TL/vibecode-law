<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

describe('auth', function () {
    test('requires authentication', function () {
        $response = get(route('showcase.manage.create'));

        $response->assertRedirect(route('login'));
    });

    test('allows authenticated user', function () {
        /** @var User */
        $user = User::factory()->create();

        actingAs($user);

        $response = get(route('showcase.manage.create'));

        $response->assertOk();
    });

    test('requires email verification', function () {
        /** @var User */
        $user = User::factory()->unverified()->create();

        actingAs($user);

        $response = get(route('showcase.manage.create'));

        $response->assertRedirect(route('verification.notice'));
    });
});

describe('data structure', function () {
    test('returns correct component', function () {
        /** @var User */
        $user = User::factory()->create();

        actingAs($user);

        $response = get(route('showcase.manage.create'));

        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('showcase/user/create')
        );
    });
});
