<?php

use App\Enums\ShowcaseStatus;
use App\Models\Showcase\Showcase;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

describe('auth', function () {
    test('requires authentication', function () {
        $response = get(route('user-area.showcases.index'));

        $response->assertRedirect(route('login'));
    });
});

describe('filtering', function () {
    test('shows only authenticated users showcases', function () {
        /** @var User */
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Showcase::factory()->count(3)->for($user)->create();
        Showcase::factory()->count(5)->for($otherUser)->create();

        actingAs($user);

        $response = get(route('user-area.showcases.index'));

        $response->assertOk();

        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->has('showcases', 3)
        );
    });
});

describe('ordering', function () {
    test('showcases are ordered by latest first', function () {
        /** @var User */
        $user = User::factory()->create();

        $oldest = Showcase::factory()->for($user)->create([
            'created_at' => now()->subDays(10),
        ]);

        $newest = Showcase::factory()->for($user)->create([
            'created_at' => now()->subDays(1),
        ]);

        $middle = Showcase::factory()->for($user)->create([
            'created_at' => now()->subDays(5),
        ]);

        actingAs($user);

        $response = get(route('user-area.showcases.index'));

        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->has('showcases.0', fn (AssertableInertia $showcase) => $showcase
                ->where('id', $newest->id)
                ->etc()
            )
            ->has('showcases.1', fn (AssertableInertia $showcase) => $showcase
                ->where('id', $middle->id)
                ->etc()
            )
            ->has('showcases.2', fn (AssertableInertia $showcase) => $showcase
                ->where('id', $oldest->id)
                ->etc()
            )
        );
    });
});

describe('relationships and counts', function () {
    test('includes thumbnail_url when set', function () {
        /** @var User */
        $user = User::factory()->create();
        $showcase = Showcase::factory()->for($user)->create([
            'thumbnail_extension' => 'jpg',
        ]);

        actingAs($user);

        $response = get(route('user-area.showcases.index'));

        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->has('showcases.0', fn (AssertableInertia $showcaseProp) => $showcaseProp
                ->has('thumbnail_url')
                ->etc()
            )
        );
    });

    test('includes upvotes count', function () {
        /** @var User */
        $user = User::factory()->create();
        $showcase = Showcase::factory()->for($user)->approved()->create();

        // Add upvoters
        $upvoters = User::factory()->count(3)->create();
        $showcase->upvoters()->attach($upvoters);

        actingAs($user);

        $response = get(route('user-area.showcases.index'));

        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->has('showcases.0', fn (AssertableInertia $showcaseProp) => $showcaseProp
                ->where('upvotes_count', 3)
                ->etc()
            )
        );
    });
});

describe('data structure', function () {
    test('returns only required fields for showcases', function () {
        /** @var User */
        $user = User::factory()->create();

        $showcase = Showcase::factory()->for($user)->create([
            'title' => 'My Project',
            'tagline' => 'My project tagline',
            'view_count' => 50,
            'status' => ShowcaseStatus::Draft,
            'thumbnail_extension' => 'jpg',
            'thumbnail_crop' => ['x' => 0, 'y' => 25, 'width' => 300, 'height' => 200],
        ]);

        // Add upvoters
        $upvoters = User::factory()->count(2)->create();
        $showcase->upvoters()->attach($upvoters);

        actingAs($user);

        $response = get(route('user-area.showcases.index'));

        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->has('showcases.0', fn (AssertableInertia $showcaseProp) => $showcaseProp
                ->where('id', $showcase->id)
                ->where('slug', $showcase->slug)
                ->where('title', 'My Project')
                ->where('tagline', 'My project tagline')
                ->where('view_count', 50)
                ->where('upvotes_count', 2)
                ->where('rejection_reason', null)
                ->where('status', ShowcaseStatus::Draft->forFrontend())
                ->has('thumbnail_url')
                ->where('thumbnail_rect_string', 'rect=0,25,300,200')
            )
        );
    });

    test('includes rejection reason for rejected showcases', function () {
        /** @var User */
        $user = User::factory()->create();

        Showcase::factory()->for($user)->rejected()->create([
            'rejection_reason' => 'Missing required information',
        ]);

        actingAs($user);

        $response = get(route('user-area.showcases.index'));

        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->has('showcases.0', fn (AssertableInertia $showcaseProp) => $showcaseProp
                ->where('rejection_reason', 'Missing required information')
                ->etc()
            )
        );
    });

    test('returns showcases with all statuses for the user', function () {
        /** @var User */
        $user = User::factory()->create();

        Showcase::factory()->for($user)->draft()->create();
        Showcase::factory()->for($user)->pending()->create();
        Showcase::factory()->for($user)->approved()->create();
        Showcase::factory()->for($user)->rejected()->create();

        actingAs($user);

        $response = get(route('user-area.showcases.index'));

        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->has('showcases', 4)
        );
    });
});
