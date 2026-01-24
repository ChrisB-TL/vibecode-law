<?php

use App\Models\User;

use function Pest\Laravel\actingAs;

describe('public pages', function () {
    it('loads without smoke', function () {
        visit([
            '/',
            '/showcase',
            '/login',
            '/register',
            '/forgot-password',
        ])->assertNoSmoke();
    });
});

describe('authenticated user pages', function () {
    it('loads without smoke', function () {
        actingAs(User::factory()->create());

        visit([
            '/user-area/profile',
            '/user-area/showcases',
            '/user-area/password',
            '/user-area/appearance',
            '/showcase/create',
        ])->assertNoSmoke();
    });
});

describe('staff pages', function () {
    it('loads without smoke', function () {
        actingAs(User::factory()->moderator()->create());

        visit([
            '/staff/practice-areas',
            '/staff/showcase-moderation',
        ])->assertNoSmoke();
    });
});
