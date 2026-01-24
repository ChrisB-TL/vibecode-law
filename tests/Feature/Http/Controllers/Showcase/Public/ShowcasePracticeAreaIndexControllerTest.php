<?php

use App\Models\PracticeArea;
use App\Models\Showcase\Showcase;
use App\Models\User;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

test('practiceArea filters showcases by practice area', function () {
    $targetPracticeArea = PracticeArea::factory()->create(['slug' => 'legal-research']);
    $otherPracticeArea = PracticeArea::factory()->create(['slug' => 'contracts']);

    $matchingShowcase = Showcase::factory()->approved()->create();
    $matchingShowcase->practiceAreas()->sync([$targetPracticeArea->id]);

    $nonMatchingShowcase = Showcase::factory()->approved()->create();
    $nonMatchingShowcase->practiceAreas()->sync([$otherPracticeArea->id]);

    get(route('showcase.practice-area', ['practiceArea' => 'legal-research']))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('showcase/public/index')
            ->has('showcases.data', 1)
            ->where('showcases.data.0.id', $matchingShowcase->id)
        );
});

test('practiceArea returns 404 for non-existent practice area', function () {
    get(route('showcase.practice-area', ['practiceArea' => 'non-existent']))
        ->assertNotFound();
});

test('practiceArea returns active filter with practice area info', function () {
    $practiceArea = PracticeArea::factory()->create(['name' => 'Legal Research', 'slug' => 'legal-research']);

    get(route('showcase.practice-area', ['practiceArea' => 'legal-research']))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('activeFilter.type', 'practice_area')
            ->where('activeFilter.practiceArea.id', $practiceArea->id)
            ->where('activeFilter.practiceArea.name', 'Legal Research')
            ->where('activeFilter.practiceArea.slug', 'legal-research')
        );
});

test('practiceArea paginates results with 20 per page', function () {
    $practiceArea = PracticeArea::factory()->create();

    $showcases = Showcase::factory()->count(25)->approved()->create();
    foreach ($showcases as $showcase) {
        $showcase->practiceAreas()->sync([$practiceArea->id]);
    }

    get(route('showcase.practice-area', ['practiceArea' => $practiceArea->slug]))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('showcases.data', 20)
            ->where('showcases.meta.per_page', 20)
            ->where('showcases.meta.total', 25)
        );
});

test('practiceArea orders showcases by upvotes count descending', function () {
    $practiceArea = PracticeArea::factory()->create();

    $lowUpvotes = Showcase::factory()->approved()->hasUpvoters(1)->create();
    $lowUpvotes->practiceAreas()->sync([$practiceArea->id]);

    $highUpvotes = Showcase::factory()->approved()->hasUpvoters(10)->create();
    $highUpvotes->practiceAreas()->sync([$practiceArea->id]);

    get(route('showcase.practice-area', ['practiceArea' => $practiceArea->slug]))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('showcases.data.0.id', $highUpvotes->id)
            ->where('showcases.data.1.id', $lowUpvotes->id)
        );
});

test('practiceArea excludes non-publicly-visible showcases', function () {
    $practiceArea = PracticeArea::factory()->create();

    $approved = Showcase::factory()->approved()->create();
    $approved->practiceAreas()->sync([$practiceArea->id]);

    $draft = Showcase::factory()->draft()->create();
    $draft->practiceAreas()->sync([$practiceArea->id]);

    $pending = Showcase::factory()->pending()->create();
    $pending->practiceAreas()->sync([$practiceArea->id]);

    get(route('showcase.practice-area', ['practiceArea' => $practiceArea->slug]))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('showcases.data', 1)
        );
});

test('practiceArea returns practice areas for filtering', function () {
    $practiceArea = PracticeArea::factory()->create();
    PracticeArea::factory()->count(4)->create();

    get(route('showcase.practice-area', ['practiceArea' => $practiceArea->slug]))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('availableFilters.practiceAreas', 5)
        );
});

test('practiceArea includes has_upvoted for authenticated users', function () {
    /** @var User $user */
    $user = User::factory()->create();
    $practiceArea = PracticeArea::factory()->create();

    $showcase = Showcase::factory()->approved()->create();
    $showcase->practiceAreas()->sync([$practiceArea->id]);
    $showcase->upvoters()->attach($user);

    actingAs($user);

    get(route('showcase.practice-area', ['practiceArea' => $practiceArea->slug]))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('showcases.data.0.has_upvoted', true)
        );
});

test('practiceArea does not include has_upvoted for guests', function () {
    $practiceArea = PracticeArea::factory()->create();

    $showcase = Showcase::factory()->approved()->create();
    $showcase->practiceAreas()->sync([$practiceArea->id]);

    get(route('showcase.practice-area', ['practiceArea' => $practiceArea->slug]))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->missing('showcases.data.0.has_upvoted')
        );
});
