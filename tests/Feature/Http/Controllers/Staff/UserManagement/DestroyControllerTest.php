<?php

use App\Actions\User\DeleteUserAction;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\delete;
use function Pest\Laravel\mock;

describe('auth', function () {
    test('requires authentication', function () {
        $user = User::factory()->create();

        $response = delete(route('staff.users.destroy', $user));

        $response->assertRedirect(route('login'));
    });

    test('allows admin to delete users', function () {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        actingAs($admin);

        $response = delete(route('staff.users.destroy', $user));

        $response->assertRedirect(route('staff.users.index'));
    });

    test('does not allow moderators to delete users', function () {
        $moderator = User::factory()->moderator()->create();
        $user = User::factory()->create();

        actingAs($moderator);

        delete(route('staff.users.destroy', $user))
            ->assertForbidden();
    });

    test('does not allow regular users to delete users', function () {
        /** @var User */
        $regularUser = User::factory()->create();
        $user = User::factory()->create();

        actingAs($regularUser);

        delete(route('staff.users.destroy', $user))
            ->assertForbidden();
    });

    test('does not allow admin to delete themselves', function () {
        $admin = User::factory()->admin()->create();

        actingAs($admin);

        delete(route('staff.users.destroy', $admin))
            ->assertForbidden();
    });
});

describe('deletion', function () {
    test('deletes the user', function () {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        actingAs($admin);

        delete(route('staff.users.destroy', $user));

        assertDatabaseMissing('users', ['id' => $user->id]);
    });

    test('calls DeleteUserAction with the user', function () {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        mock(DeleteUserAction::class)
            ->shouldReceive('delete')
            ->once()
            ->withArgs(fn (User $passedUser) => $passedUser->is($user))
            ->andReturnNull();

        actingAs($admin);

        delete(route('staff.users.destroy', $user));
    });

    test('redirects to users index with success message', function () {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        actingAs($admin);

        $response = delete(route('staff.users.destroy', $user));

        $response->assertRedirect(route('staff.users.index'));
        $response->assertSessionHas('flash.message', ['message' => 'User deleted successfully.', 'type' => 'success']);
    });
});
