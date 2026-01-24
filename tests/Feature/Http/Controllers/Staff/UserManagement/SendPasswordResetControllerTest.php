<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;

describe('auth', function () {
    test('requires authentication', function () {
        $user = User::factory()->create();

        $response = post(route('staff.users.send-password-reset', $user));

        $response->assertRedirect(route('login'));
    });

    test('allows admin to send password reset', function () {
        Notification::fake();

        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        actingAs($admin);

        $response = post(route('staff.users.send-password-reset', $user));

        $response->assertRedirect();
    });

    test('does not allow moderators to send password reset', function () {
        $moderator = User::factory()->moderator()->create();
        $user = User::factory()->create();

        actingAs($moderator);

        post(route('staff.users.send-password-reset', $user))
            ->assertForbidden();
    });

    test('does not allow regular users to send password reset', function () {
        /** @var User */
        $regularUser = User::factory()->create();
        $user = User::factory()->create();

        actingAs($regularUser);

        post(route('staff.users.send-password-reset', $user))
            ->assertForbidden();
    });
});

describe('password reset', function () {
    test('sends password reset notification', function () {
        Notification::fake();

        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        actingAs($admin);

        post(route('staff.users.send-password-reset', $user));

        Notification::assertSentTo($user, ResetPassword::class);
    });

    test('returns success message', function () {
        Notification::fake();

        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        actingAs($admin);

        $response = post(route('staff.users.send-password-reset', $user));

        $response->assertSessionHas('flash.message', ['message' => 'Password reset email sent successfully.', 'type' => 'success']);
    });
});
