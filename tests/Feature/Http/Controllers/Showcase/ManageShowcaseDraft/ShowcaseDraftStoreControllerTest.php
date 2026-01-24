<?php

use App\Actions\ShowcaseDraft\CreateShowcaseDraftAction;
use App\Models\Showcase\Showcase;
use App\Models\Showcase\ShowcaseDraft;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\mock;
use function Pest\Laravel\post;

beforeEach(function () {
    Storage::fake('public');
});

describe('auth', function () {
    test('requires authentication', function () {
        $showcase = Showcase::factory()->approved()->create();

        $response = post(route('showcase.draft.store', $showcase));

        $response->assertRedirect(route('login'));
    });

    test('only showcase owner can create a draft', function () {
        /** @var User */
        $otherUser = User::factory()->create();
        $showcase = Showcase::factory()->approved()->create();

        actingAs($otherUser);

        $response = post(route('showcase.draft.store', $showcase));

        $response->assertForbidden();
    });

    test('owner can create draft for their approved showcase', function () {
        /** @var User */
        $owner = User::factory()->create();
        $showcase = Showcase::factory()->approved()->for($owner, 'user')->create();

        actingAs($owner);

        $response = post(route('showcase.draft.store', $showcase));

        $response->assertRedirect();
    });
});

describe('draft creation', function () {
    test('calls CreateShowcaseDraftAction when creating draft', function () {
        /** @var User */
        $owner = User::factory()->create();
        $showcase = Showcase::factory()->approved()->for($owner, 'user')->create();

        mock(CreateShowcaseDraftAction::class)
            ->shouldReceive('create')
            ->once()
            ->withArgs(fn (Showcase $passedShowcase) => $passedShowcase->is($showcase))
            ->andReturn(ShowcaseDraft::factory()->for($showcase, 'showcase')->make(['id' => 1]));

        actingAs($owner);

        post(route('showcase.draft.store', $showcase));
    });

    test('creates a draft for approved showcase', function () {
        /** @var User */
        $owner = User::factory()->create();
        $showcase = Showcase::factory()->approved()->for($owner, 'user')->create();

        actingAs($owner);

        post(route('showcase.draft.store', $showcase));

        expect(ShowcaseDraft::where('showcase_id', $showcase->id)->exists())->toBeTrue();
    });

    test('cannot create draft for non-approved showcase', function () {
        /** @var User */
        $owner = User::factory()->create();
        $showcase = Showcase::factory()->pending()->for($owner, 'user')->create();

        actingAs($owner);

        $response = post(route('showcase.draft.store', $showcase));

        $response->assertForbidden();
    });

    test('cannot create draft if one already exists', function () {
        /** @var User */
        $owner = User::factory()->create();
        $showcase = Showcase::factory()->approved()->for($owner, 'user')->create();
        ShowcaseDraft::factory()->create(['showcase_id' => $showcase->id]);

        actingAs($owner);

        $response = post(route('showcase.draft.store', $showcase));

        // Should redirect to existing draft
        $response->assertRedirect();
        expect(ShowcaseDraft::where('showcase_id', $showcase->id)->count())->toBe(1);
    });

    test('redirects to draft edit page after creation', function () {
        /** @var User */
        $owner = User::factory()->create();
        $showcase = Showcase::factory()->approved()->for($owner, 'user')->create();

        actingAs($owner);

        $response = post(route('showcase.draft.store', $showcase));

        $draft = ShowcaseDraft::where('showcase_id', $showcase->id)->first();
        $response->assertRedirect(route('showcase.draft.edit', $draft));
    });
});

describe('blocked users', function () {
    test('blocked user cannot create draft', function () {
        /** @var User */
        $owner = User::factory()->blockedFromSubmissions()->create();
        $showcase = Showcase::factory()->approved()->for($owner, 'user')->create();

        actingAs($owner);

        $response = post(route('showcase.draft.store', $showcase));

        $response->assertForbidden();
    });
});
