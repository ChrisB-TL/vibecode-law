<?php

use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\get;

test('returns 200 status', function () {
    get('/resources')
        ->assertOk();
});

test('renders correct Inertia component', function () {
    get('/resources')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('resources/index')
        );
});

test('returns correct props', function () {
    get('/resources')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('resources/index')
            ->has('title')
            ->has('content')
            ->has('children', 3)
        );
});

test('returns correct title', function () {
    get('/resources')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('title', 'Resources')
        );
});

test('content contains expected HTML', function () {
    get('/resources')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('content', fn (string $content) => str_contains($content, '<h1>'))
        );
});

test('children array has correct structure', function () {
    get('/resources')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('children', 3)
            ->has('children.0', fn (AssertableInertia $child) => $child
                ->where('name', 'What is Vibecoding?')
                ->where('slug', 'what-is-vibecoding')
                ->where('summary', 'Discover what vibecoding is and how AI-assisted development is transforming the way legal professionals build software.')
                ->where('icon', 'lightbulb')
                ->where('route', route(name: 'resources.show', parameters: ['slug' => 'what-is-vibecoding']))
            )
            ->has('children.1', fn (AssertableInertia $child) => $child
                ->where('name', 'Start Vibecoding')
                ->where('slug', 'start-vibecoding')
                ->where('summary', 'A practical guide to choosing platforms and tools to begin your vibecoding journey in legal tech.')
                ->where('icon', 'play')
                ->where('route', route(name: 'resources.show', parameters: ['slug' => 'start-vibecoding']))
            )
            ->has('children.2', fn (AssertableInertia $child) => $child
                ->where('name', 'Risks of Vibecoding')
                ->where('slug', 'risks-of-vibecoding')
                ->where('summary', 'Understand the technical, security, and professional risks of AI-generated code and how to mitigate them.')
                ->where('icon', 'alert-triangle')
                ->where('route', route(name: 'resources.show', parameters: ['slug' => 'risks-of-vibecoding']))
            )
        );
});
