<?php

use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

test('avatar returns null when avatar_path is null', function () {
    $user = User::factory()->make(['avatar_path' => null]);

    expect($user->avatar)->toBeNull();
});

test('avatar returns storage url when image transform base url is not set', function () {
    Storage::fake('public');
    Config::set('services.image-transform.base_url', null);

    $user = User::factory()->make(['avatar_path' => 'avatars/test-avatar.jpg']);

    expect($user->avatar)->toBe(Storage::disk('public')->url('avatars/test-avatar.jpg'));
});

test('avatar returns image transform url when image transform base url is set', function () {
    Config::set('services.image-transform.base_url', 'https://images.example.com');

    $user = User::factory()->make(['avatar_path' => 'avatars/test-avatar.jpg']);

    expect($user->avatar)->toBe('https://images.example.com/avatars/test-avatar.jpg');
});
