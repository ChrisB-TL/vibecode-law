<?php

use App\Actions\ShowcaseDraft\DiscardShowcaseDraftAction;
use App\Models\Showcase\Showcase;
use App\Models\Showcase\ShowcaseDraft;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\delete;
use function Pest\Laravel\mock;

beforeEach(function () {
    Storage::fake('public');
});

describe('auth', function () {
    test('requires authentication', function () {
        $showcase = Showcase::factory()->approved()->create();
        $draft = ShowcaseDraft::factory()->for($showcase, 'showcase')->create();

        $response = delete(route('showcase.draft.destroy', $draft));

        $response->assertRedirect(route('login'));
    });

    test('requires email verification', function () {
        /** @var User */
        $owner = User::factory()->unverified()->create();
        $showcase = Showcase::factory()->approved()->for($owner, 'user')->create();
        $draft = ShowcaseDraft::factory()->for($showcase, 'showcase')->create();

        actingAs($owner);

        $response = delete(route('showcase.draft.destroy', $draft));

        $response->assertRedirect(route('verification.notice'));
    });

    test('only owner can delete draft', function () {
        /** @var User */
        $otherUser = User::factory()->create();
        $showcase = Showcase::factory()->approved()->create();
        $draft = ShowcaseDraft::factory()->for($showcase, 'showcase')->create();

        actingAs($otherUser);

        $response = delete(route('showcase.draft.destroy', $draft));

        $response->assertForbidden();
    });

    test('owner can delete their draft', function () {
        /** @var User */
        $owner = User::factory()->create();
        $showcase = Showcase::factory()->approved()->for($owner, 'user')->create();
        $draft = ShowcaseDraft::factory()->for($showcase, 'showcase')->create();

        actingAs($owner);

        $response = delete(route('showcase.draft.destroy', $draft));

        $response->assertRedirect();
    });

    test('admin can delete any draft', function () {
        /** @var User */
        $admin = User::factory()->admin()->create();
        $showcase = Showcase::factory()->approved()->create();
        $draft = ShowcaseDraft::factory()->for($showcase, 'showcase')->create();

        actingAs($admin);

        $response = delete(route('showcase.draft.destroy', $draft));

        $response->assertRedirect();
    });
});

describe('deletion', function () {
    test('calls DiscardShowcaseDraftAction with the draft', function () {
        /** @var User */
        $owner = User::factory()->create();
        $showcase = Showcase::factory()->approved()->for($owner, 'user')->create();
        $draft = ShowcaseDraft::factory()->for($showcase, 'showcase')->create();

        mock(DiscardShowcaseDraftAction::class)
            ->shouldReceive('discard')
            ->once()
            ->withArgs(fn (ShowcaseDraft $passedDraft) => $passedDraft->is($draft))
            ->andReturnNull();

        actingAs($owner);

        delete(route('showcase.draft.destroy', $draft));
    });

    test('deletes draft from database', function () {
        /** @var User */
        $owner = User::factory()->create();
        $showcase = Showcase::factory()->approved()->for($owner, 'user')->create();
        $draft = ShowcaseDraft::factory()->for($showcase, 'showcase')->create();

        actingAs($owner);

        delete(route('showcase.draft.destroy', $draft));

        assertDatabaseMissing('showcase_drafts', [
            'id' => $draft->id,
        ]);
    });

    test('can delete draft with any status', function () {
        /** @var User */
        $owner = User::factory()->create();
        $showcase = Showcase::factory()->approved()->for($owner, 'user')->create();
        $pendingDraft = ShowcaseDraft::factory()->pending()->for($showcase, 'showcase')->create();

        actingAs($owner);

        $response = delete(route('showcase.draft.destroy', $pendingDraft));

        $response->assertRedirect();
        assertDatabaseMissing('showcase_drafts', [
            'id' => $pendingDraft->id,
        ]);
    });
});

describe('response', function () {
    test('redirects to showcases index', function () {
        /** @var User */
        $owner = User::factory()->create();
        $showcase = Showcase::factory()->approved()->for($owner, 'user')->create();
        $draft = ShowcaseDraft::factory()->for($showcase, 'showcase')->create();

        actingAs($owner);

        $response = delete(route('showcase.draft.destroy', $draft));

        $response->assertRedirect(route('user-area.showcases.index'));
    });

    test('includes success flash message', function () {
        /** @var User */
        $owner = User::factory()->create();
        $showcase = Showcase::factory()->approved()->for($owner, 'user')->create();
        $draft = ShowcaseDraft::factory()->for($showcase, 'showcase')->create();

        actingAs($owner);

        $response = delete(route('showcase.draft.destroy', $draft));

        $response->assertSessionHas('flash.message', ['message' => 'Draft discarded.', 'type' => 'success']);
    });
});
