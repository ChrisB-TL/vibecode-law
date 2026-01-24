<?php

use App\Actions\ShowcaseDraft\SubmitShowcaseDraftAction;
use App\Models\Showcase\Showcase;
use App\Models\Showcase\ShowcaseDraft;
use App\Models\User;
use App\Notifications\ShowcaseDraft\ShowcaseDraftSubmittedForApproval;
use Illuminate\Support\Facades\Notification;
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
        $draft = ShowcaseDraft::factory()->for($showcase, 'showcase')->create();

        $response = post(route('showcase.draft.submit', $draft));

        $response->assertRedirect(route('login'));
    });

    test('requires email verification', function () {
        /** @var User */
        $owner = User::factory()->unverified()->create();
        $showcase = Showcase::factory()->approved()->for($owner, 'user')->create();
        $draft = ShowcaseDraft::factory()->for($showcase, 'showcase')->create();

        actingAs($owner);

        $response = post(route('showcase.draft.submit', $draft));

        $response->assertRedirect(route('verification.notice'));
    });

    test('only owner can submit draft', function () {
        /** @var User */
        $otherUser = User::factory()->create();
        $showcase = Showcase::factory()->approved()->create();
        $draft = ShowcaseDraft::factory()->for($showcase, 'showcase')->create();

        actingAs($otherUser);

        $response = post(route('showcase.draft.submit', $draft));

        $response->assertForbidden();
    });

    test('owner can submit their draft', function () {
        Notification::fake();

        /** @var User */
        $owner = User::factory()->create();
        $showcase = Showcase::factory()->approved()->for($owner, 'user')->create();
        $draft = ShowcaseDraft::factory()->for($showcase, 'showcase')->create();

        actingAs($owner);

        $response = post(route('showcase.draft.submit', $draft));

        $response->assertRedirect();
    });

    test('blocked user cannot submit draft', function () {
        /** @var User */
        $owner = User::factory()->blockedFromSubmissions()->create();
        $showcase = Showcase::factory()->approved()->for($owner, 'user')->create();
        $draft = ShowcaseDraft::factory()->for($showcase, 'showcase')->create();

        actingAs($owner);

        $response = post(route('showcase.draft.submit', $draft));

        $response->assertForbidden();
    });
});

describe('status restrictions', function () {
    test('can submit draft with Draft status', function () {
        Notification::fake();

        /** @var User */
        $owner = User::factory()->create();
        $showcase = Showcase::factory()->approved()->for($owner, 'user')->create();
        $draft = ShowcaseDraft::factory()->draft()->for($showcase, 'showcase')->create();

        actingAs($owner);

        $response = post(route('showcase.draft.submit', $draft));

        $response->assertRedirect(route('user-area.showcases.index'));
    });

    test('can submit draft with Rejected status', function () {
        Notification::fake();

        /** @var User */
        $owner = User::factory()->create();
        $showcase = Showcase::factory()->approved()->for($owner, 'user')->create();
        $draft = ShowcaseDraft::factory()->rejected()->for($showcase, 'showcase')->create();

        actingAs($owner);

        $response = post(route('showcase.draft.submit', $draft));

        $response->assertRedirect(route('user-area.showcases.index'));
    });

    test('cannot submit draft with Pending status', function () {
        /** @var User */
        $owner = User::factory()->create();
        $showcase = Showcase::factory()->approved()->for($owner, 'user')->create();
        $draft = ShowcaseDraft::factory()->pending()->for($showcase, 'showcase')->create();

        actingAs($owner);

        $response = post(route('showcase.draft.submit', $draft));

        $response->assertForbidden();
    });
});

describe('submission', function () {
    test('calls SubmitShowcaseDraftAction with the draft', function () {
        Notification::fake();

        /** @var User */
        $owner = User::factory()->create();
        $showcase = Showcase::factory()->approved()->for($owner, 'user')->create();
        $draft = ShowcaseDraft::factory()->for($showcase, 'showcase')->create();

        mock(SubmitShowcaseDraftAction::class)
            ->shouldReceive('submit')
            ->once()
            ->withArgs(fn (ShowcaseDraft $passedDraft) => $passedDraft->is($draft));

        actingAs($owner);

        post(route('showcase.draft.submit', $draft));
    });

    test('sends notification to admins', function () {
        Notification::fake();

        /** @var User */
        $owner = User::factory()->create();
        $admin = User::factory()->admin()->create();

        $showcase = Showcase::factory()->approved()->for($owner, 'user')->create();
        $draft = ShowcaseDraft::factory()->for($showcase, 'showcase')->create();

        actingAs($owner);

        post(route('showcase.draft.submit', $draft));

        Notification::assertSentTo($admin, ShowcaseDraftSubmittedForApproval::class);
    });

    test('sends notification to users with showcase.approve-reject permission', function () {
        Notification::fake();

        /** @var User */
        $owner = User::factory()->create();
        // Create a user with direct permission (controller queries permissions relationship directly)
        $userWithPermission = User::factory()->create();
        $userWithPermission->givePermissionTo('showcase.approve-reject');

        $showcase = Showcase::factory()->approved()->for($owner, 'user')->create();
        $draft = ShowcaseDraft::factory()->for($showcase, 'showcase')->create();

        actingAs($owner);

        post(route('showcase.draft.submit', $draft));

        Notification::assertSentTo($userWithPermission, ShowcaseDraftSubmittedForApproval::class);
    });

    test('does not send notification to regular users', function () {
        Notification::fake();

        /** @var User */
        $owner = User::factory()->create();
        $regularUser = User::factory()->create();

        $showcase = Showcase::factory()->approved()->for($owner, 'user')->create();
        $draft = ShowcaseDraft::factory()->for($showcase, 'showcase')->create();

        actingAs($owner);

        post(route('showcase.draft.submit', $draft));

        Notification::assertNotSentTo($regularUser, ShowcaseDraftSubmittedForApproval::class);
    });
});

describe('response', function () {
    test('redirects to showcases index', function () {
        Notification::fake();

        /** @var User */
        $owner = User::factory()->create();
        $showcase = Showcase::factory()->approved()->for($owner, 'user')->create();
        $draft = ShowcaseDraft::factory()->for($showcase, 'showcase')->create();

        actingAs($owner);

        $response = post(route('showcase.draft.submit', $draft));

        $response->assertRedirect(route('user-area.showcases.index'));
    });

    test('includes success flash message', function () {
        Notification::fake();

        /** @var User */
        $owner = User::factory()->create();
        $showcase = Showcase::factory()->approved()->for($owner, 'user')->create();
        $draft = ShowcaseDraft::factory()->for($showcase, 'showcase')->create();

        actingAs($owner);

        $response = post(route('showcase.draft.submit', $draft));

        $response->assertSessionHas('flash.message', ['message' => 'Draft submitted for approval.', 'type' => 'success']);
    });
});
