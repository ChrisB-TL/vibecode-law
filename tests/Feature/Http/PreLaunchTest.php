<?php

use App\Models\Showcase\Showcase;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

describe('homepage pre-launch', function () {
    beforeEach(function () {
        Config::set('app.launched', false);
    });

    test('returns featured showcases instead of showcases by month', function () {
        $featured = Showcase::factory()->featured()->create();
        $nonFeatured = Showcase::factory()->approved()->create([
            'submitted_date' => now(),
        ]);

        get('/')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('home')
                ->has('featuredShowcases')
                ->missing('showcasesByMonth')
            );
    });

    test('featured showcases are ordered by upvotes descending', function () {
        $upvoters = User::factory()->count(5)->create();

        $mostUpvoted = Showcase::factory()->featured()->create([
            'title' => 'Most Upvoted',
        ]);
        $mostUpvoted->upvoters()->attach($upvoters->pluck('id'));

        $leastUpvoted = Showcase::factory()->featured()->create([
            'title' => 'Least Upvoted',
        ]);
        // No upvotes for this one

        $response = get('/');
        $response->assertOk();

        $featuredShowcases = $response->original->getData()['page']['props']['featuredShowcases'];

        expect($featuredShowcases)->toHaveCount(2);
        expect($featuredShowcases[0]['id'])->toBe($mostUpvoted->id);
        expect($featuredShowcases[1]['id'])->toBe($leastUpvoted->id);
    });

    test('only returns featured showcases, not non-featured', function () {
        $featured = Showcase::factory()->featured()->create();
        $nonFeatured = Showcase::factory()->approved()->create([
            'submitted_date' => now(),
        ]);

        $response = get('/');
        $response->assertOk();

        $featuredShowcases = $response->original->getData()['page']['props']['featuredShowcases'];

        expect($featuredShowcases)->toHaveCount(1);
        expect($featuredShowcases[0]['id'])->toBe($featured->id);
    });

    test('returns minimal fields for featured showcases', function () {
        /** @var User */
        $user = User::factory()->create();

        $showcase = Showcase::factory()
            ->featured()
            ->create([
                'title' => 'Test Showcase',
                'slug' => 'test-showcase',
                'tagline' => 'Test tagline',
                'thumbnail_extension' => 'jpg',
                'thumbnail_crop' => ['x' => 10, 'y' => 20, 'width' => 100, 'height' => 100],
            ]);

        $showcase->upvoters()->attach($user);

        actingAs($user)
            ->get('/')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('home')
                ->has('featuredShowcases.0', fn (AssertableInertia $showcaseProp) => $showcaseProp
                    ->where('id', $showcase->id)
                    ->where('slug', 'test-showcase')
                    ->where('title', 'Test Showcase')
                    ->where('tagline', 'Test tagline')
                    ->has('thumbnail_url')
                    ->where('thumbnail_rect_string', 'rect=10,20,100,100')
                    ->where('upvotes_count', 1)
                    ->where('has_upvoted', true)
                )
            );
    });
});

describe('showcase index routes pre-launch', function () {
    beforeEach(function () {
        Config::set('app.launched', false);
    });

    test('showcase index returns 404', function () {
        Showcase::factory()->approved()->create([
            'submitted_date' => now(),
        ]);

        get(route('showcase.index'))
            ->assertNotFound();
    });

    test('showcase practice area filter returns 404', function () {
        $showcase = Showcase::factory()->approved()->create([
            'submitted_date' => now(),
        ]);

        $practiceArea = $showcase->practiceAreas->first();

        get(route('showcase.practice-area', ['practiceArea' => $practiceArea]))
            ->assertNotFound();
    });

    test('showcase month filter returns 404', function () {
        Showcase::factory()->approved()->create([
            'submitted_date' => now(),
        ]);

        get(route('showcase.month', ['month' => now()->format('Y-m')]))
            ->assertNotFound();
    });
});

describe('showcase show pre-launch', function () {
    beforeEach(function () {
        Config::set('app.launched', false);
    });

    test('featured showcase is accessible to guests', function () {
        $showcase = Showcase::factory()->featured()->create();

        get(route('showcase.show', $showcase))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('showcase/public/show')
                ->has('showcase')
            );
    });

    test('non-featured showcase returns 404 for guests', function () {
        $showcase = Showcase::factory()->approved()->create([
            'submitted_date' => now(),
        ]);

        get(route('showcase.show', $showcase))
            ->assertNotFound();
    });

    test('non-featured showcase returns 404 for non-owner authenticated users', function () {
        /** @var User */
        $user = User::factory()->create();
        $showcase = Showcase::factory()->approved()->create([
            'submitted_date' => now(),
        ]);

        actingAs($user);

        get(route('showcase.show', $showcase))
            ->assertNotFound();
    });

    test('owner can view their own non-featured showcase', function () {
        /** @var User */
        $user = User::factory()->create();
        $showcase = Showcase::factory()->approved()->for($user)->create([
            'submitted_date' => now(),
        ]);

        actingAs($user);

        get(route('showcase.show', $showcase))
            ->assertOk();
    });

    test('admin can view any non-featured showcase', function () {
        /** @var User */
        $admin = User::factory()->admin()->create();
        $showcase = Showcase::factory()->approved()->create([
            'submitted_date' => now(),
        ]);

        actingAs($admin);

        get(route('showcase.show', $showcase))
            ->assertOk();
    });

    test('moderator can view any non-featured showcase', function () {
        $moderator = User::factory()->moderator()->create();
        $showcase = Showcase::factory()->approved()->create([
            'submitted_date' => now(),
        ]);

        actingAs($moderator);

        get(route('showcase.show', $showcase))
            ->assertOk();
    });
});

describe('unaffected routes pre-launch', function () {
    beforeEach(function () {
        Config::set('app.launched', false);
    });

    test('login page is accessible', function () {
        get(route('login'))
            ->assertOk();
    });

    test('register page is accessible', function () {
        get(route('register'))
            ->assertOk();
    });

    test('about page is accessible', function () {
        get(route('about.index'))
            ->assertOk();
    });

    test('showcase create page is accessible when logged in', function () {
        /** @var User */
        $user = User::factory()->create();

        actingAs($user);

        get(route('showcase.manage.create'))
            ->assertOk();
    });
});

describe('post-launch behavior', function () {
    beforeEach(function () {
        Config::set('app.launched', true);
    });

    test('homepage shows showcases by month', function () {
        Showcase::factory()->approved()->create([
            'submitted_date' => now(),
        ]);

        get('/')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('home')
                ->has('showcasesByMonth')
                ->missing('featuredShowcases')
            );
    });

    test('showcase index is accessible', function () {
        Showcase::factory()->approved()->create([
            'submitted_date' => now(),
        ]);

        get(route('showcase.index'))
            ->assertOk();
    });

    test('non-featured showcase is accessible to guests', function () {
        $showcase = Showcase::factory()->approved()->create([
            'submitted_date' => now(),
        ]);

        get(route('showcase.show', $showcase))
            ->assertOk();
    });

    test('showcase practice area filter is accessible', function () {
        $showcase = Showcase::factory()->approved()->create([
            'submitted_date' => now(),
        ]);

        $practiceArea = $showcase->practiceAreas->first();

        get(route('showcase.practice-area', ['practiceArea' => $practiceArea]))
            ->assertOk();
    });

    test('showcase month filter is accessible', function () {
        Showcase::factory()->approved()->create([
            'submitted_date' => now(),
        ]);

        get(route('showcase.month', ['month' => now()->format('Y-m')]))
            ->assertOk();
    });
});
