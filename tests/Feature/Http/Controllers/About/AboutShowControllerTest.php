<?php

use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\get;

test('returns 200 for valid about page slugs', function (string $slug) {
    get("/about/{$slug}")
        ->assertOk();
})->with([
    'submission-process',
    'moderation-process',
    'contact',
]);

test('renders correct Inertia component', function () {
    get('/about/submission-process')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('about/show')
        );
});

test('returns correct props', function () {
    get('/about/submission-process')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('about/show')
            ->has('title')
            ->has('slug')
            ->has('content')
        );
});

test('content is rendered HTML', function () {
    get('/about/submission-process')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('about/show')
            ->where('content', fn (string $content) => str_contains($content, '<h1>'))
        );
});

test('returns 404 for invalid slugs', function (string $slug) {
    get("/about/{$slug}")
        ->assertNotFound();
})->with([
    'invalid-page',
    'nonexistent',
    'terms-of-use',
    'index',
]);

test('each configured about page returns correct data', function (string $slug) {
    get("/about/{$slug}")
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('about/show')
            ->has('title')
            ->where('slug', $slug)
            ->has('content')
        );
})->with([
    'submission-process',
    'moderation-process',
    'contact',
]);
