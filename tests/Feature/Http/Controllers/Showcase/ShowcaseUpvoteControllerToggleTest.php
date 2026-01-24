<?php

use App\Models\Showcase\Showcase;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\post;

describe('auth', function () {
    test('requires authentication', function () {
        $showcase = Showcase::factory()->approved()->create();

        $response = post(route('showcase.manage.toggle-upvote', $showcase));

        $response->assertRedirect(route('login'));
    });

    test('allows authenticated user to upvote approved showcase', function () {
        /** @var User */
        $user = User::factory()->create();
        $showcase = Showcase::factory()->approved()->create();

        actingAs($user);

        $response = post(route('showcase.manage.toggle-upvote', $showcase));

        $response->assertRedirect();
    });
});

describe('constraints', function () {
    test('can upvote own showcase', function () {
        /** @var User */
        $user = User::factory()->create();
        $showcase = Showcase::factory()->approved()->for($user, 'user')->create();

        actingAs($user);

        post(route('showcase.manage.toggle-upvote', $showcase))
            ->assertRedirect()
            ->assertSessionHas('flash.message', ['message' => 'Showcase upvoted.', 'type' => 'success']);

        assertDatabaseHas('showcase_upvotes', [
            'user_id' => $user->id,
            'showcase_id' => $showcase->id,
        ]);
    });

    test('cannot upvote draft showcase', function () {
        /** @var User */
        $user = User::factory()->create();
        $showcase = Showcase::factory()->draft()->create();

        actingAs($user);

        post(route('showcase.manage.toggle-upvote', $showcase))
            ->assertNotFound();

        assertDatabaseMissing('showcase_upvotes', [
            'user_id' => $user->id,
            'showcase_id' => $showcase->id,
        ]);
    });

    test('cannot upvote pending showcase', function () {
        /** @var User */
        $user = User::factory()->create();
        $showcase = Showcase::factory()->pending()->create();

        actingAs($user);

        post(route('showcase.manage.toggle-upvote', $showcase))
            ->assertNotFound();

        assertDatabaseMissing('showcase_upvotes', [
            'user_id' => $user->id,
            'showcase_id' => $showcase->id,
        ]);
    });

    test('cannot upvote rejected showcase', function () {
        /** @var User */
        $user = User::factory()->create();
        $showcase = Showcase::factory()->rejected()->create();

        actingAs($user);

        post(route('showcase.manage.toggle-upvote', $showcase))
            ->assertNotFound();

        assertDatabaseMissing('showcase_upvotes', [
            'user_id' => $user->id,
            'showcase_id' => $showcase->id,
        ]);
    });

    test('can upvote approved showcase', function () {
        /** @var User */
        $user = User::factory()->create();
        $showcase = Showcase::factory()->approved()->create();

        actingAs($user);

        post(route('showcase.manage.toggle-upvote', $showcase));

        assertDatabaseHas('showcase_upvotes', [
            'user_id' => $user->id,
            'showcase_id' => $showcase->id,
        ]);
    });
});

describe('upvote toggle', function () {
    test('creates upvote when user has not upvoted', function () {
        /** @var User */
        $user = User::factory()->create();
        $showcase = Showcase::factory()->approved()->create();

        actingAs($user);

        post(route('showcase.manage.toggle-upvote', $showcase));

        assertDatabaseHas('showcase_upvotes', [
            'user_id' => $user->id,
            'showcase_id' => $showcase->id,
        ]);
    });

    test('removes upvote when user has already upvoted', function () {
        /** @var User */
        $user = User::factory()->create();
        $showcase = Showcase::factory()->approved()->create();
        $showcase->upvoters()->attach($user->id);

        actingAs($user);

        post(route('showcase.manage.toggle-upvote', $showcase));

        assertDatabaseMissing('showcase_upvotes', [
            'user_id' => $user->id,
            'showcase_id' => $showcase->id,
        ]);
    });

    test('can toggle upvote multiple times', function () {
        /** @var User */
        $user = User::factory()->create();
        $showcase = Showcase::factory()->approved()->create();

        actingAs($user);

        // First upvote
        post(route('showcase.manage.toggle-upvote', $showcase));
        assertDatabaseHas('showcase_upvotes', [
            'user_id' => $user->id,
            'showcase_id' => $showcase->id,
        ]);

        // Remove upvote
        post(route('showcase.manage.toggle-upvote', $showcase));
        assertDatabaseMissing('showcase_upvotes', [
            'user_id' => $user->id,
            'showcase_id' => $showcase->id,
        ]);

        // Upvote again
        post(route('showcase.manage.toggle-upvote', $showcase));
        assertDatabaseHas('showcase_upvotes', [
            'user_id' => $user->id,
            'showcase_id' => $showcase->id,
        ]);
    });

    test('tracks upvote with timestamp', function () {
        /** @var User */
        $user = User::factory()->create();
        $showcase = Showcase::factory()->approved()->create();

        actingAs($user);

        post(route('showcase.manage.toggle-upvote', $showcase));

        $upvote = $showcase->upvoters()->where('user_id', $user->id)->first();
        expect($upvote)->not->toBeNull();
        expect($upvote->pivot->created_at)->not->toBeNull();
    });
});

describe('upvote count', function () {
    test('increments upvoters count when upvoting', function () {
        /** @var User */
        $user = User::factory()->create();
        $showcase = Showcase::factory()->approved()->create();

        expect($showcase->upvoters()->count())->toBe(0);

        actingAs($user);

        post(route('showcase.manage.toggle-upvote', $showcase));

        expect($showcase->upvoters()->count())->toBe(1);
    });

    test('decrements upvoters count when removing upvote', function () {
        /** @var User */
        $user = User::factory()->create();
        $showcase = Showcase::factory()->approved()->create();
        $showcase->upvoters()->attach($user->id);

        expect($showcase->upvoters()->count())->toBe(1);

        actingAs($user);

        post(route('showcase.manage.toggle-upvote', $showcase));

        expect($showcase->upvoters()->count())->toBe(0);
    });

    test('multiple users can upvote same showcase', function () {
        /** @var User */
        $user1 = User::factory()->create();
        /** @var User */
        $user2 = User::factory()->create();
        /** @var User */
        $user3 = User::factory()->create();
        $showcase = Showcase::factory()->approved()->create();

        actingAs($user1);
        post(route('showcase.manage.toggle-upvote', $showcase));

        actingAs($user2);
        post(route('showcase.manage.toggle-upvote', $showcase));

        actingAs($user3);
        post(route('showcase.manage.toggle-upvote', $showcase));

        expect($showcase->upvoters()->count())->toBe(3);
    });
});

describe('response', function () {
    test('redirects back after upvoting', function () {
        /** @var User */
        $user = User::factory()->create();
        $showcase = Showcase::factory()->approved()->create();

        actingAs($user);

        $response = post(route('showcase.manage.toggle-upvote', $showcase));

        $response->assertRedirect();
    });

    test('redirects back after removing upvote', function () {
        /** @var User */
        $user = User::factory()->create();
        $showcase = Showcase::factory()->approved()->create();
        $showcase->upvoters()->attach($user->id);

        actingAs($user);

        $response = post(route('showcase.manage.toggle-upvote', $showcase));

        $response->assertRedirect();
    });

    test('includes success message when upvoting', function () {
        /** @var User */
        $user = User::factory()->create();
        $showcase = Showcase::factory()->approved()->create();

        actingAs($user);

        $response = post(route('showcase.manage.toggle-upvote', $showcase));

        $response->assertSessionHas('flash.message', ['message' => 'Showcase upvoted.', 'type' => 'success']);
    });

    test('includes success message when removing upvote', function () {
        /** @var User */
        $user = User::factory()->create();
        $showcase = Showcase::factory()->approved()->create();
        $showcase->upvoters()->attach($user->id);

        actingAs($user);

        $response = post(route('showcase.manage.toggle-upvote', $showcase));

        $response->assertSessionHas('flash.message', ['message' => 'Upvote removed.', 'type' => 'success']);
    });
});
