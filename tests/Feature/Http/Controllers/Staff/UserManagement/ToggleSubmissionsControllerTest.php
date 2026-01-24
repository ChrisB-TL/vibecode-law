<?php

use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;

describe('auth', function () {
    test('requires authentication', function () {
        $user = User::factory()->create();

        $response = post(route('staff.users.toggle-submissions', $user));

        $response->assertRedirect(route('login'));
    });

    test('allows admin to toggle submissions', function () {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        actingAs($admin);

        $response = post(route('staff.users.toggle-submissions', $user));

        $response->assertRedirect();
    });

    test('does not allow moderators to toggle submissions', function () {
        $moderator = User::factory()->moderator()->create();
        $user = User::factory()->create();

        actingAs($moderator);

        post(route('staff.users.toggle-submissions', $user))
            ->assertForbidden();
    });

    test('does not allow regular users to toggle submissions', function () {
        /** @var User */
        $regularUser = User::factory()->create();
        $user = User::factory()->create();

        actingAs($regularUser);

        post(route('staff.users.toggle-submissions', $user))
            ->assertForbidden();
    });
});

describe('toggle', function () {
    test('blocks user from submissions when not blocked', function () {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create(['blocked_from_submissions_at' => null]);

        expect($user->blocked_from_submissions_at)->toBeNull();

        actingAs($admin);

        post(route('staff.users.toggle-submissions', $user));

        expect($user->fresh()->blocked_from_submissions_at)->not->toBeNull();
    });

    test('unblocks user from submissions when blocked', function () {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->blockedFromSubmissions()->create();

        expect($user->blocked_from_submissions_at)->not->toBeNull();

        actingAs($admin);

        post(route('staff.users.toggle-submissions', $user));

        expect($user->fresh()->blocked_from_submissions_at)->toBeNull();
    });

    test('returns success message when blocking', function () {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create(['blocked_from_submissions_at' => null]);

        actingAs($admin);

        $response = post(route('staff.users.toggle-submissions', $user));

        $response->assertSessionHas('flash.message', ['message' => 'User has been blocked from submissions.', 'type' => 'success']);
    });

    test('returns success message when unblocking', function () {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->blockedFromSubmissions()->create();

        actingAs($admin);

        $response = post(route('staff.users.toggle-submissions', $user));

        $response->assertSessionHas('flash.message', ['message' => 'User has been unblocked from submissions.', 'type' => 'success']);
    });
});
