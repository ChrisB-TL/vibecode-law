<?php

use App\Actions\User\DeleteUserAction;
use App\Models\Showcase\Showcase;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

test('deletes the user', function () {
    $user = User::factory()->create();
    $action = new DeleteUserAction;

    $action->delete(user: $user);

    assertDatabaseMissing('users', ['id' => $user->id]);
});

test('deletes user avatar from storage', function () {
    Storage::fake('public');

    $user = User::factory()->create([
        'avatar_path' => 'avatars/test-avatar.jpg',
    ]);

    Storage::disk('public')->put('avatars/test-avatar.jpg', 'fake-content');
    Storage::disk('public')->assertExists('avatars/test-avatar.jpg');

    $action = new DeleteUserAction;
    $action->delete(user: $user);

    Storage::disk('public')->assertMissing('avatars/test-avatar.jpg');
});

test('nullifies user_id on showcases', function () {
    $user = User::factory()->create();
    $showcase = Showcase::factory()->create(['user_id' => $user->id]);

    expect($showcase->user_id)->toBe($user->id);

    $action = new DeleteUserAction;
    $action->delete(user: $user);

    assertDatabaseHas('showcases', [
        'id' => $showcase->id,
        'user_id' => null,
    ]);
});

test('handles users without avatars', function () {
    Storage::fake('public');

    $user = User::factory()->create(['avatar_path' => null]);

    $action = new DeleteUserAction;
    $action->delete(user: $user);

    assertDatabaseMissing('users', ['id' => $user->id]);
});

test('handles users without showcases', function () {
    $user = User::factory()->create();

    $action = new DeleteUserAction;
    $action->delete(user: $user);

    assertDatabaseMissing('users', ['id' => $user->id]);
});

test('does not delete showcases', function () {
    $user = User::factory()->create();
    $showcase = Showcase::factory()->create(['user_id' => $user->id]);

    $action = new DeleteUserAction;
    $action->delete(user: $user);

    assertDatabaseHas('showcases', ['id' => $showcase->id]);
});
