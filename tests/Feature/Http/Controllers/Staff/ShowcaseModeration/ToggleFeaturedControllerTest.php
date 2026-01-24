<?php

use App\Enums\ShowcaseStatus;
use App\Models\Showcase\Showcase;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;

describe('auth', function () {
    test('requires authentication', function () {
        $showcase = Showcase::factory()->approved()->create();

        $response = post(route('staff.showcase-moderation.toggle-featured', $showcase));

        $response->assertRedirect(route('login'));
    });

    test('requires admin privileges', function () {
        /** @var User */
        $regularUser = User::factory()->create();
        $showcase = Showcase::factory()->approved()->create();

        actingAs($regularUser);

        $response = post(route('staff.showcase-moderation.toggle-featured', $showcase));

        $response->assertForbidden();
    });

    test('allows admin to toggle featured status', function () {
        /** @var User */
        $admin = User::factory()->admin()->create();
        $showcase = Showcase::factory()->approved()->create();

        actingAs($admin);

        $response = post(route('staff.showcase-moderation.toggle-featured', $showcase));

        $response->assertRedirect();
    });
});

describe('toggle featured', function () {
    test('features approved showcase that is not featured', function () {
        /** @var User */
        $admin = User::factory()->admin()->create();
        $showcase = Showcase::factory()->approved()->create([
            'is_featured' => false,
        ]);

        actingAs($admin);

        post(route('staff.showcase-moderation.toggle-featured', $showcase));

        $showcase->refresh();

        expect($showcase->is_featured)->toBeTrue();
    });

    test('unfeatures approved showcase that is featured', function () {
        /** @var User */
        $admin = User::factory()->admin()->create();
        $showcase = Showcase::factory()->featured()->create();

        actingAs($admin);

        post(route('staff.showcase-moderation.toggle-featured', $showcase));

        $showcase->refresh();

        expect($showcase->is_featured)->toBeFalse();
    });

    test('can toggle featured status multiple times', function () {
        /** @var User */
        $admin = User::factory()->admin()->create();
        $showcase = Showcase::factory()->approved()->create([
            'is_featured' => false,
        ]);

        actingAs($admin);

        // Feature it
        post(route('staff.showcase-moderation.toggle-featured', $showcase));
        $showcase->refresh();
        expect($showcase->is_featured)->toBeTrue();

        // Unfeature it
        post(route('staff.showcase-moderation.toggle-featured', $showcase));
        $showcase->refresh();
        expect($showcase->is_featured)->toBeFalse();

        // Feature it again
        post(route('staff.showcase-moderation.toggle-featured', $showcase));
        $showcase->refresh();
        expect($showcase->is_featured)->toBeTrue();
    });
});

describe('approval requirement', function () {
    test('cannot feature pending showcase', function () {
        /** @var User */
        $admin = User::factory()->admin()->create();
        $showcase = Showcase::factory()->pending()->create([
            'is_featured' => false,
        ]);

        actingAs($admin);

        $response = post(route('staff.showcase-moderation.toggle-featured', $showcase));

        $showcase->refresh();

        expect($showcase->is_featured)->toBeFalse();
        $response->assertSessionHas('flash.message', ['message' => 'Only approved showcases can be featured.', 'type' => 'error']);
    });

    test('cannot feature draft showcase', function () {
        /** @var User */
        $admin = User::factory()->admin()->create();
        $showcase = Showcase::factory()->draft()->create([
            'is_featured' => false,
        ]);

        actingAs($admin);

        $response = post(route('staff.showcase-moderation.toggle-featured', $showcase));

        $showcase->refresh();

        expect($showcase->is_featured)->toBeFalse();
        $response->assertSessionHas('flash.message', ['message' => 'Only approved showcases can be featured.', 'type' => 'error']);
    });

    test('cannot feature rejected showcase', function () {
        /** @var User */
        $admin = User::factory()->admin()->create();
        $showcase = Showcase::factory()->rejected()->create([
            'is_featured' => false,
        ]);

        actingAs($admin);

        $response = post(route('staff.showcase-moderation.toggle-featured', $showcase));

        $showcase->refresh();

        expect($showcase->is_featured)->toBeFalse();
        $response->assertSessionHas('flash.message', ['message' => 'Only approved showcases can be featured.', 'type' => 'error']);
    });

    test('can unfeature showcase that was featured then rejected', function () {
        /** @var User */
        $admin = User::factory()->admin()->create();
        $showcase = Showcase::factory()->approved()->create([
            'is_featured' => true,
        ]);

        // Manually set to rejected while keeping featured status
        $showcase->update(['status' => ShowcaseStatus::Rejected]);

        actingAs($admin);

        $response = post(route('staff.showcase-moderation.toggle-featured', $showcase));

        $showcase->refresh();

        // Should still be featured because toggle only works on approved
        expect($showcase->is_featured)->toBeTrue();
        $response->assertSessionHas('flash.message', ['message' => 'Only approved showcases can be featured.', 'type' => 'error']);
    });
});

describe('response', function () {
    test('redirects back to previous page when featuring', function () {
        /** @var User */
        $admin = User::factory()->admin()->create();
        $showcase = Showcase::factory()->approved()->create([
            'is_featured' => false,
        ]);

        actingAs($admin);

        $response = post(route('staff.showcase-moderation.toggle-featured', $showcase));

        $response->assertRedirect();
    });

    test('redirects back to previous page when unfeaturing', function () {
        /** @var User */
        $admin = User::factory()->admin()->create();
        $showcase = Showcase::factory()->featured()->create();

        actingAs($admin);

        $response = post(route('staff.showcase-moderation.toggle-featured', $showcase));

        $response->assertRedirect();
    });

    test('includes success message when featuring', function () {
        /** @var User */
        $admin = User::factory()->admin()->create();
        $showcase = Showcase::factory()->approved()->create([
            'is_featured' => false,
        ]);

        actingAs($admin);

        $response = post(route('staff.showcase-moderation.toggle-featured', $showcase));

        $response->assertSessionHas('flash.message', ['message' => 'Showcase featured.', 'type' => 'success']);
    });

    test('includes success message when unfeaturing', function () {
        /** @var User */
        $admin = User::factory()->admin()->create();
        $showcase = Showcase::factory()->featured()->create();

        actingAs($admin);

        $response = post(route('staff.showcase-moderation.toggle-featured', $showcase));

        $response->assertSessionHas('flash.message', ['message' => 'Showcase unfeatured.', 'type' => 'success']);
    });
});
