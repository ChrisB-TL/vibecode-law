<?php

use App\Models\User;
use Illuminate\Support\Facades\Gate;

use function Pest\Laravel\actingAs;

describe('admin gate', function () {
    test('admin users can perform any action', function () {
        $admin = User::factory()->admin()->create();

        Gate::define('test-ability', fn (User $user) => false);

        actingAs($admin);

        expect(Gate::allows('test-ability'))->toBeTrue();
        expect(Gate::allows('any-random-ability'))->toBeTrue();
    });

    test('non admin users cannot perform restricted actions', function () {
        $user = User::factory()->create();

        Gate::define('test-ability', fn (User $user) => false);

        actingAs($user);

        expect(Gate::allows('test-ability'))->toBeFalse();
    });

    test('non admin users can perform explicitly allowed actions', function () {
        $user = User::factory()->create();

        Gate::define('allowed-ability', fn (User $user) => true);

        actingAs($user);

        expect(Gate::allows('allowed-ability'))->toBeTrue();
    });
});
