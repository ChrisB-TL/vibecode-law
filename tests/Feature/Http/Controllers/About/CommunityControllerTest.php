<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia;

use function Pest\Laravel\get;

test('returns 200 status', function () {
    get('/about/the-community')
        ->assertOk();
});

test('renders correct Inertia component', function () {
    get('/about/the-community')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('about/community')
        );
});

test('returns correct props structure', function () {
    get('/about/the-community')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('about/community')
            ->has('title')
            ->has('coreTeam')
            ->has('collaborators')
        );
});

test('returns correct title', function () {
    get('/about/the-community')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('title', 'The Community')
        );
});

test('displays core team members', function () {
    $coreTeamMember = User::factory()->coreTeam(role: 'Founder')->create([
        'first_name' => 'John',
        'last_name' => 'Doe',
    ]);

    get('/about/the-community')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('coreTeam', 1)
            ->has('coreTeam.0', fn (AssertableInertia $member) => $member
                ->where('first_name', 'John')
                ->where('last_name', 'Doe')
                ->where('team_role', 'Founder')
                ->etc()
            )
        );
});

test('displays collaborators', function () {
    $collaborator = User::factory()->collaborator(role: 'Designer')->create([
        'first_name' => 'Jane',
        'last_name' => 'Smith',
    ]);

    get('/about/the-community')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('collaborators', 1)
            ->has('collaborators.0', fn (AssertableInertia $member) => $member
                ->where('first_name', 'Jane')
                ->where('last_name', 'Smith')
                ->where('team_role', 'Designer')
                ->etc()
            )
        );
});

test('orders core team members by first name', function () {
    User::factory()->coreTeam()->create(['first_name' => 'Zara']);
    User::factory()->coreTeam()->create(['first_name' => 'Alice']);

    get('/about/the-community')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('coreTeam', 2)
            ->where('coreTeam.0.first_name', 'Alice')
            ->where('coreTeam.1.first_name', 'Zara')
        );
});
