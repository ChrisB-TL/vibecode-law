<?php

use App\Models\Showcase\Showcase;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;

describe('auth', function () {
    test('requires authentication', function () {
        $showcase = Showcase::factory()->approved()->create();

        post(route('showcase.manage.dismiss-celebration', $showcase))
            ->assertRedirect(route('login'));
    });

    test('requires email verification', function () {
        /** @var User */
        $user = User::factory()->unverified()->create();
        $showcase = Showcase::factory()->approved()->for($user, 'user')->create();

        actingAs($user);

        post(route('showcase.manage.dismiss-celebration', $showcase))
            ->assertRedirect(route('verification.notice'));
    });

    test('allows owner to dismiss celebration', function () {
        /** @var User */
        $user = User::factory()->create();
        $showcase = Showcase::factory()->approved()->for($user, 'user')->create();

        actingAs($user);

        post(route('showcase.manage.dismiss-celebration', $showcase))
            ->assertRedirect();
    });

    test('prevents non-owner from dismissing celebration', function () {
        /** @var User */
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $showcase = Showcase::factory()->approved()->for($otherUser, 'user')->create();

        actingAs($user);

        post(route('showcase.manage.dismiss-celebration', $showcase))
            ->assertForbidden();
    });

    test('prevents admin from dismissing celebration for other users showcase', function () {
        /** @var User */
        $admin = User::factory()->admin()->create();
        $otherUser = User::factory()->create();
        $showcase = Showcase::factory()->approved()->for($otherUser, 'user')->create();

        actingAs($admin);

        post(route('showcase.manage.dismiss-celebration', $showcase))
            ->assertForbidden();
    });
});

describe('functionality', function () {
    test('sets approval_celebrated_at timestamp', function () {
        /** @var User */
        $user = User::factory()->create();
        $showcase = Showcase::factory()->approved()->for($user, 'user')->create();

        expect($showcase->approval_celebrated_at)->toBeNull();

        actingAs($user);

        post(route('showcase.manage.dismiss-celebration', $showcase));

        $showcase->refresh();

        expect($showcase->approval_celebrated_at)->not->toBeNull();
    });

    test('can dismiss celebration multiple times', function () {
        /** @var User */
        $user = User::factory()->create();
        $showcase = Showcase::factory()->approved()->for($user, 'user')->create([
            'approval_celebrated_at' => now()->subDay(),
        ]);

        actingAs($user);

        post(route('showcase.manage.dismiss-celebration', $showcase))
            ->assertRedirect();
    });
});

describe('response', function () {
    test('redirects back after dismissing', function () {
        /** @var User */
        $user = User::factory()->create();
        $showcase = Showcase::factory()->approved()->for($user, 'user')->create();

        actingAs($user);

        post(route('showcase.manage.dismiss-celebration', $showcase))
            ->assertRedirect();
    });
});
