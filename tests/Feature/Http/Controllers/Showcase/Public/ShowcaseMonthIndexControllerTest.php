<?php

use App\Models\Showcase\Showcase;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

test('month filters showcases by submitted_date month', function () {
    $januaryShowcase = Showcase::factory()->approved()->create(['submitted_date' => '2025-01-15']);
    $februaryShowcase = Showcase::factory()->approved()->create(['submitted_date' => '2025-02-15']);

    get(route('showcase.month', ['month' => '2025-01']))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('showcase/public/index')
            ->has('showcases.data', 1)
            ->where('showcases.data.0.id', $januaryShowcase->id)
        );
});

test('month returns 404 for invalid month format', function () {
    get(route('showcase.month', ['month' => 'invalid']))
        ->assertNotFound();
});

test('month returns 404 for month format YYYY-M instead of YYYY-MM', function () {
    get(route('showcase.month', ['month' => '2025-1']))
        ->assertNotFound();
});

test('month returns 404 for invalid month number 13', function () {
    get(route('showcase.month', ['month' => '2025-13']))
        ->assertNotFound();
});

test('month returns 404 for invalid month number 00', function () {
    get(route('showcase.month', ['month' => '2025-00']))
        ->assertNotFound();
});

test('month returns active filter with month info', function () {
    get(route('showcase.month', ['month' => '2025-01']))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('activeFilter.type', 'month')
            ->where('activeFilter.month', '2025-01')
            ->missing('activeFilter.displayMonth')
        );
});

test('month paginates results with 20 per page', function () {
    Showcase::factory()->count(25)->approved()->create(['submitted_date' => '2025-01-15']);

    get(route('showcase.month', ['month' => '2025-01']))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('showcases.data', 20)
            ->where('showcases.meta.per_page', 20)
            ->where('showcases.meta.total', 25)
        );
});

test('month orders showcases by upvotes count descending', function () {
    $lowUpvotes = Showcase::factory()->approved()->hasUpvoters(1)->create(['submitted_date' => '2025-01-05']);
    $highUpvotes = Showcase::factory()->approved()->hasUpvoters(10)->create(['submitted_date' => '2025-01-25']);

    get(route('showcase.month', ['month' => '2025-01']))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('showcases.data.0.id', $highUpvotes->id)
            ->where('showcases.data.1.id', $lowUpvotes->id)
        );
});

test('month excludes non-publicly-visible showcases', function () {
    Showcase::factory()->approved()->create(['submitted_date' => '2025-01-15']);
    Showcase::factory()->draft()->create(['submitted_date' => '2025-01-15']);
    Showcase::factory()->pending()->create(['submitted_date' => '2025-01-15']);
    Showcase::factory()->rejected()->create(['submitted_date' => '2025-01-15']);

    get(route('showcase.month', ['month' => '2025-01']))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('showcases.data', 1)
        );
});

test('month returns empty results for month with no showcases', function () {
    get(route('showcase.month', ['month' => '2020-01']))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('showcases.data', 0)
        );
});

test('month returns months for filtering', function () {
    Showcase::factory()->approved()->create(['submitted_date' => '2025-01-15']);
    Showcase::factory()->approved()->create(['submitted_date' => '2025-02-15']);
    Showcase::factory()->approved()->create(['submitted_date' => '2025-03-15']);

    get(route('showcase.month', ['month' => '2025-01']))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('availableFilters.months', 3)
            ->where('availableFilters.months.0', '2025-03')
            ->where('availableFilters.months.1', '2025-02')
            ->where('availableFilters.months.2', '2025-01')
        );
});

test('month includes has_upvoted for authenticated users', function () {
    /** @var User $user */
    $user = User::factory()->create();
    $showcase = Showcase::factory()->approved()->create(['submitted_date' => '2025-01-15']);
    $showcase->upvoters()->attach($user);

    actingAs($user);

    get(route('showcase.month', ['month' => '2025-01']))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('showcases.data.0.has_upvoted', true)
        );
});

test('month does not include has_upvoted for guests', function () {
    Showcase::factory()->approved()->create(['submitted_date' => '2025-01-15']);

    get(route('showcase.month', ['month' => '2025-01']))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->missing('showcases.data.0.has_upvoted')
        );
});

test('month includes showcases from start to end of month', function () {
    $startOfMonth = Showcase::factory()->approved()->create(['submitted_date' => '2025-01-01 00:00:00']);
    $endOfMonth = Showcase::factory()->approved()->create(['submitted_date' => '2025-01-31 23:59:59']);
    $nextMonth = Showcase::factory()->approved()->create(['submitted_date' => '2025-02-01 00:00:00']);

    get(route('showcase.month', ['month' => '2025-01']))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('showcases.data', 2)
        );
});
