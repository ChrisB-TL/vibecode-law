<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
});

it('allows user to upload an avatar', function () {
    /** @var User */
    $user = User::factory()->create();

    actingAs($user)->patch(route('user-area.profile.update'), [
        'first_name' => $user->first_name,
        'last_name' => $user->last_name,
        'handle' => $user->handle,
        'email' => $user->email,
        'avatar' => UploadedFile::fake()->image('avatar.jpg', 100, 100),
    ])->assertRedirect(route('user-area.profile.edit'));

    $user->refresh();

    expect($user->avatar_path)->not()->toBeNull();
    Storage::disk('public')->assertExists($user->avatar_path);
});

it('stores avatar in users/avatars directory', function () {
    /** @var User */
    $user = User::factory()->create();

    actingAs($user)->patch(route('user-area.profile.update'), [
        'first_name' => $user->first_name,
        'last_name' => $user->last_name,
        'handle' => $user->handle,
        'email' => $user->email,
        'avatar' => UploadedFile::fake()->image('avatar.png', 100, 100),
    ])->assertRedirect(route('user-area.profile.edit'));

    $user->refresh();

    expect($user->avatar_path)->toStartWith('users/avatars/');
    expect($user->avatar_path)->toEndWith('.png');
});

it('deletes old avatar when uploading new one', function () {
    /** @var User */
    $user = User::factory()->create([
        'avatar_path' => 'users/avatars/old-avatar.jpg',
    ]);
    Storage::disk('public')->put('users/avatars/old-avatar.jpg', 'old content');

    actingAs($user)->patch(route('user-area.profile.update'), [
        'first_name' => $user->first_name,
        'last_name' => $user->last_name,
        'handle' => $user->handle,
        'email' => $user->email,
        'avatar' => UploadedFile::fake()->image('new-avatar.jpg', 100, 100),
    ])->assertRedirect(route('user-area.profile.edit'));

    Storage::disk('public')->assertMissing('users/avatars/old-avatar.jpg');
    Storage::disk('public')->assertExists($user->refresh()->avatar_path);
});

it('validates avatar is an image', function () {
    /** @var User */
    $user = User::factory()->create();

    actingAs($user)->patch(route('user-area.profile.update'), [
        'first_name' => $user->first_name,
        'last_name' => $user->last_name,
        'handle' => $user->handle,
        'email' => $user->email,
        'avatar' => UploadedFile::fake()->create('document.pdf', 100),
    ])->assertSessionHasErrors('avatar');
});

it('validates avatar file type', function (string $extension) {
    /** @var User */
    $user = User::factory()->create();

    actingAs($user)->patch(route('user-area.profile.update'), [
        'first_name' => $user->first_name,
        'last_name' => $user->last_name,
        'handle' => $user->handle,
        'email' => $user->email,
        'avatar' => UploadedFile::fake()->create("file.{$extension}", 100),
    ])->assertSessionHasErrors('avatar');
})->with([
    'svg',
    'bmp',
    'tiff',
]);

it('validates avatar max size of 2MB', function () {
    /** @var User */
    $user = User::factory()->create();

    actingAs($user)->patch(route('user-area.profile.update'), [
        'first_name' => $user->first_name,
        'last_name' => $user->last_name,
        'handle' => $user->handle,
        'email' => $user->email,
        'avatar' => UploadedFile::fake()->image('large-avatar.jpg')->size(3000),
    ])->assertSessionHasErrors('avatar');
});

it('accepts valid image types', function (string $extension) {
    /** @var User */
    $user = User::factory()->create();

    actingAs($user)->patch(route('user-area.profile.update'), [
        'first_name' => $user->first_name,
        'last_name' => $user->last_name,
        'handle' => $user->handle,
        'email' => $user->email,
        'avatar' => UploadedFile::fake()->image("avatar.{$extension}", 100, 100),
    ])->assertSessionHasNoErrors();

    expect($user->refresh()->avatar_path)->not()->toBeNull();
})->with([
    'jpg',
    'jpeg',
    'png',
    'gif',
    'webp',
]);

it('allows updating profile without avatar', function () {
    /** @var User */
    $user = User::factory()->create([
        'avatar_path' => 'users/avatars/existing.jpg',
    ]);
    Storage::disk('public')->put('users/avatars/existing.jpg', 'content');

    actingAs($user)->patch(route('user-area.profile.update'), [
        'first_name' => 'Updated',
        'last_name' => 'Name',
        'handle' => $user->handle,
        'email' => $user->email,
    ])->assertSessionHasNoErrors();

    $user->refresh();
    expect($user->first_name)->toBe('Updated');
    expect($user->avatar_path)->toBe('users/avatars/existing.jpg');
    Storage::disk('public')->assertExists('users/avatars/existing.jpg');
});

it('allows user to remove their avatar', function () {
    /** @var User */
    $user = User::factory()->create([
        'avatar_path' => 'users/avatars/existing.jpg',
    ]);
    Storage::disk('public')->put('users/avatars/existing.jpg', 'content');

    actingAs($user)->patch(route('user-area.profile.update'), [
        'first_name' => $user->first_name,
        'last_name' => $user->last_name,
        'handle' => $user->handle,
        'email' => $user->email,
        'remove_avatar' => true,
    ])->assertRedirect(route('user-area.profile.edit'));

    $user->refresh();
    expect($user->avatar_path)->toBeNull();
    Storage::disk('public')->assertMissing('users/avatars/existing.jpg');
});

it('ignores remove_avatar when user has no avatar', function () {
    /** @var User */
    $user = User::factory()->create([
        'avatar_path' => null,
    ]);

    actingAs($user)->patch(route('user-area.profile.update'), [
        'first_name' => $user->first_name,
        'last_name' => $user->last_name,
        'handle' => $user->handle,
        'email' => $user->email,
        'remove_avatar' => true,
    ])->assertRedirect(route('user-area.profile.edit'));

    expect($user->refresh()->avatar_path)->toBeNull();
});

it('prioritizes remove_avatar over new avatar upload', function () {
    /** @var User */
    $user = User::factory()->create([
        'avatar_path' => 'users/avatars/existing.jpg',
    ]);
    Storage::disk('public')->put('users/avatars/existing.jpg', 'content');

    actingAs($user)->patch(route('user-area.profile.update'), [
        'first_name' => $user->first_name,
        'last_name' => $user->last_name,
        'handle' => $user->handle,
        'email' => $user->email,
        'avatar' => UploadedFile::fake()->image(name: 'new-avatar.jpg', width: 100, height: 100),
        'remove_avatar' => true,
    ])->assertRedirect(route('user-area.profile.edit'));

    $user->refresh();
    expect($user->avatar_path)->toBeNull();
    Storage::disk('public')->assertMissing('users/avatars/existing.jpg');
});
