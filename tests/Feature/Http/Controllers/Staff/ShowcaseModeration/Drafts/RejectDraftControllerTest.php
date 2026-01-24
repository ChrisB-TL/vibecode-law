<?php

use App\Enums\ShowcaseDraftStatus;
use App\Models\Showcase\Showcase;
use App\Models\Showcase\ShowcaseDraft;
use App\Models\User;
use App\Notifications\ShowcaseDraft\ShowcaseDraftRejected;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;

beforeEach(function () {
    Storage::fake('public');
});

describe('auth', function () {
    test('requires authentication', function () {
        $draft = ShowcaseDraft::factory()->pending()->create();

        $response = post(route('staff.showcase-moderation.drafts.reject', $draft), [
            'reason' => 'Test rejection reason',
        ]);

        $response->assertRedirect(route('login'));
    });

    test('requires admin privileges', function () {
        /** @var User */
        $regularUser = User::factory()->create();
        $draft = ShowcaseDraft::factory()->pending()->create();

        actingAs($regularUser);

        $response = post(route('staff.showcase-moderation.drafts.reject', $draft), [
            'reason' => 'Test rejection reason',
        ]);

        $response->assertForbidden();
    });

    test('allows admin to reject draft', function () {
        Notification::fake();

        /** @var User */
        $admin = User::factory()->admin()->create();
        $draft = ShowcaseDraft::factory()->pending()->create();

        actingAs($admin);

        $response = post(route('staff.showcase-moderation.drafts.reject', $draft), [
            'reason' => 'Test rejection reason',
        ]);

        $response->assertRedirect();
    });
});

describe('rejection', function () {
    test('updates draft status to rejected', function () {
        Notification::fake();

        /** @var User */
        $admin = User::factory()->admin()->create();
        $draft = ShowcaseDraft::factory()->pending()->create();

        actingAs($admin);

        post(route('staff.showcase-moderation.drafts.reject', $draft), [
            'reason' => 'Test rejection reason',
        ]);

        $draft->refresh();

        expect($draft->status)->toBe(ShowcaseDraftStatus::Rejected);
    });

    test('stores rejection reason', function () {
        Notification::fake();

        /** @var User */
        $admin = User::factory()->admin()->create();
        $draft = ShowcaseDraft::factory()->pending()->create();

        actingAs($admin);

        post(route('staff.showcase-moderation.drafts.reject', $draft), [
            'reason' => 'This content needs improvement.',
        ]);

        $draft->refresh();

        expect($draft->rejection_reason)->toBe('This content needs improvement.');
    });

    test('does not delete draft', function () {
        Notification::fake();

        /** @var User */
        $admin = User::factory()->admin()->create();
        $draft = ShowcaseDraft::factory()->pending()->create();
        $draftId = $draft->id;

        actingAs($admin);

        post(route('staff.showcase-moderation.drafts.reject', $draft), [
            'reason' => 'Test rejection reason',
        ]);

        expect(ShowcaseDraft::find($draftId))->not->toBeNull();
    });
});

describe('validation', function () {
    test('requires a rejection reason', function () {
        Notification::fake();

        /** @var User */
        $admin = User::factory()->admin()->create();
        $draft = ShowcaseDraft::factory()->pending()->create();

        actingAs($admin);

        $response = post(route('staff.showcase-moderation.drafts.reject', $draft), [
            'reason' => '',
        ]);

        $response->assertSessionHasErrors(['reason']);
    });
});

describe('notification', function () {
    test('sends notification to showcase owner', function () {
        Notification::fake();

        /** @var User */
        $admin = User::factory()->admin()->create();
        $owner = User::factory()->create();
        $showcase = Showcase::factory()->approved()->for($owner, 'user')->create();
        $draft = ShowcaseDraft::factory()->pending()->create([
            'showcase_id' => $showcase->id,
        ]);

        actingAs($admin);

        post(route('staff.showcase-moderation.drafts.reject', $draft), [
            'reason' => 'Test rejection reason',
        ]);

        Notification::assertSentTo($owner, ShowcaseDraftRejected::class);
    });

    test('notification contains correct draft and reason', function () {
        Notification::fake();

        /** @var User */
        $admin = User::factory()->admin()->create();
        $owner = User::factory()->create();
        $showcase = Showcase::factory()->approved()->for($owner, 'user')->create();
        $draft = ShowcaseDraft::factory()->pending()->create([
            'showcase_id' => $showcase->id,
        ]);

        actingAs($admin);

        post(route('staff.showcase-moderation.drafts.reject', $draft), [
            'reason' => 'Specific rejection reason',
        ]);

        Notification::assertSentTo(
            $owner,
            function (ShowcaseDraftRejected $notification) use ($draft) {
                return $notification->draft->id === $draft->id
                    && $notification->reason === 'Specific rejection reason';
            }
        );
    });
});

describe('response', function () {
    test('redirects back to previous page', function () {
        Notification::fake();

        /** @var User */
        $admin = User::factory()->admin()->create();
        $draft = ShowcaseDraft::factory()->pending()->create();

        actingAs($admin);

        $response = post(route('staff.showcase-moderation.drafts.reject', $draft), [
            'reason' => 'Test rejection reason',
        ]);

        $response->assertRedirect();
    });

    test('includes success message in session', function () {
        Notification::fake();

        /** @var User */
        $admin = User::factory()->admin()->create();
        $draft = ShowcaseDraft::factory()->pending()->create();

        actingAs($admin);

        $response = post(route('staff.showcase-moderation.drafts.reject', $draft), [
            'reason' => 'Test rejection reason',
        ]);

        $response->assertSessionHas('flash.message', ['message' => 'Draft rejected.', 'type' => 'success']);
    });
});
