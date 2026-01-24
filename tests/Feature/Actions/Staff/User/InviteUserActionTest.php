<?php

use App\Actions\Staff\User\InviteUserAction;
use App\Models\User;
use App\Notifications\UserInvitation;
use Illuminate\Support\Facades\Notification;

use function Pest\Laravel\assertDatabaseHas;

test('creates a new user', function () {
    Notification::fake();

    $action = app(InviteUserAction::class);

    $user = $action->invite(data: [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
        'organisation' => 'Acme Inc',
        'job_title' => 'Developer',
        'bio' => 'A developer',
        'linkedin_url' => 'https://linkedin.com/in/johndoe',
    ]);

    assertDatabaseHas('users', [
        'id' => $user->id,
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
        'organisation' => 'Acme Inc',
        'job_title' => 'Developer',
        'bio' => 'A developer',
        'linkedin_url' => 'https://linkedin.com/in/johndoe',
    ]);
});

test('creates user with null password', function () {
    Notification::fake();

    $action = app(InviteUserAction::class);

    $user = $action->invite(data: [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
    ]);

    expect($user->password)->toBeNull();
});

test('auto-generates unique handle when not provided', function () {
    Notification::fake();

    $action = app(InviteUserAction::class);

    $user = $action->invite(data: [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
    ]);

    expect($user->handle)->toStartWith('john-doe');
});

test('uses provided handle when specified', function () {
    Notification::fake();

    $action = app(InviteUserAction::class);

    $user = $action->invite(data: [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
        'handle' => 'custom-handle',
    ]);

    expect($user->handle)->toBe('custom-handle');
});

test('assigns roles to user', function () {
    Notification::fake();

    $action = app(InviteUserAction::class);

    $user = $action->invite(
        data: [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
        ],
        roles: ['Moderator'],
    );

    expect($user->hasRole('Moderator'))->toBeTrue();
});

test('does not assign roles when empty array provided', function () {
    Notification::fake();

    $action = app(InviteUserAction::class);

    $user = $action->invite(
        data: [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
        ],
        roles: [],
    );

    expect($user->roles)->toBeEmpty();
});

test('sends user invitation notification', function () {
    Notification::fake();

    $action = app(InviteUserAction::class);

    $user = $action->invite(data: [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
    ]);

    Notification::assertSentTo($user, UserInvitation::class);
});

test('invitation contains password reset token', function () {
    Notification::fake();

    $action = app(InviteUserAction::class);

    $user = $action->invite(data: [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
    ]);

    Notification::assertSentTo($user, UserInvitation::class, function (UserInvitation $notification) {
        return strlen($notification->token) > 0;
    });
});

test('returns the created user', function () {
    Notification::fake();

    $action = app(InviteUserAction::class);

    $user = $action->invite(data: [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
    ]);

    expect($user)->toBeInstanceOf(User::class);
    expect($user->exists)->toBeTrue();
});

test('handles optional fields being null', function () {
    Notification::fake();

    $action = app(InviteUserAction::class);

    $user = $action->invite(data: [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
        'organisation' => null,
        'job_title' => null,
        'bio' => null,
        'linkedin_url' => null,
    ]);

    expect($user->organisation)->toBeNull();
    expect($user->job_title)->toBeNull();
    expect($user->bio)->toBeNull();
    expect($user->linkedin_url)->toBeNull();
});
