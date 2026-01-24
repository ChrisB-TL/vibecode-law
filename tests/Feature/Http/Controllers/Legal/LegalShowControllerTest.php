<?php

use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\get;

test('returns 200 for valid legal page slugs', function (string $slug) {
    get("/legal/{$slug}")
        ->assertOk();
})->with([
    'terms-of-use',
    'privacy-notice',
    'community-guidelines',
]);

test('returns correct component and props', function () {
    get('/legal/terms-of-use')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('legal/show')
            ->where('title', 'Terms of Use')
            ->where('slug', 'terms-of-use')
            ->has('content')
        );
});

test('content is rendered HTML', function () {
    get('/legal/terms-of-use')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('legal/show')
            ->where('content', fn (string $content) => str_contains($content, '<h1>'))
        );
});

test('returns 404 for invalid slugs', function (string $slug) {
    get("/legal/{$slug}")
        ->assertNotFound();
})->with([
    'invalid-page',
    'about',
    'contact',
    'nonexistent',
]);

test('each configured legal page returns correct data', function (string $slug, string $title) {
    get("/legal/{$slug}")
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('legal/show')
            ->where('title', $title)
            ->where('slug', $slug)
            ->has('content')
        );
})->with([
    ['community-guidelines', 'Community Guidelines'],
    ['terms-of-use', 'Terms of Use'],
    ['privacy-notice', 'Privacy Notice'],
]);
