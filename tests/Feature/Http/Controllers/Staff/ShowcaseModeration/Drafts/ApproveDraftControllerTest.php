<?php

use App\Models\Showcase\Showcase;
use App\Models\Showcase\ShowcaseDraft;
use App\Models\User;
use App\Notifications\ShowcaseDraft\ShowcaseDraftApproved;
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

        $response = post(route('staff.showcase-moderation.drafts.approve', $draft));

        $response->assertRedirect(route('login'));
    });

    test('requires admin privileges', function () {
        /** @var User */
        $regularUser = User::factory()->create();
        $draft = ShowcaseDraft::factory()->pending()->create();

        actingAs($regularUser);

        $response = post(route('staff.showcase-moderation.drafts.approve', $draft));

        $response->assertForbidden();
    });

    test('allows admin to approve draft', function () {
        Notification::fake();

        /** @var User */
        $admin = User::factory()->admin()->create();
        $draft = ShowcaseDraft::factory()->pending()->create();

        actingAs($admin);

        $response = post(route('staff.showcase-moderation.drafts.approve', $draft));

        $response->assertRedirect();
    });

    test('allows moderator to approve draft', function () {
        Notification::fake();

        /** @var User */
        $moderator = User::factory()->moderator()->create();
        $draft = ShowcaseDraft::factory()->pending()->create();

        actingAs($moderator);

        $response = post(route('staff.showcase-moderation.drafts.approve', $draft));

        $response->assertRedirect();
    });
});

describe('approval', function () {
    test('applies draft changes to showcase', function () {
        Notification::fake();

        /** @var User */
        $admin = User::factory()->admin()->create();
        $showcase = Showcase::factory()->approved()->create([
            'title' => 'Original Title',
        ]);
        $draft = ShowcaseDraft::factory()->pending()->create([
            'showcase_id' => $showcase->id,
            'title' => 'Updated Title',
        ]);

        actingAs($admin);

        post(route('staff.showcase-moderation.drafts.approve', $draft));

        $showcase->refresh();

        expect($showcase->title)->toBe('Updated Title');
    });

    test('deletes draft after approval', function () {
        Notification::fake();

        /** @var User */
        $admin = User::factory()->admin()->create();
        $draft = ShowcaseDraft::factory()->pending()->create();
        $draftId = $draft->id;

        actingAs($admin);

        post(route('staff.showcase-moderation.drafts.approve', $draft));

        expect(ShowcaseDraft::find($draftId))->toBeNull();
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

        post(route('staff.showcase-moderation.drafts.approve', $draft));

        Notification::assertSentTo($owner, ShowcaseDraftApproved::class);
    });

    test('notification contains correct showcase', function () {
        Notification::fake();

        /** @var User */
        $admin = User::factory()->admin()->create();
        $owner = User::factory()->create();
        $showcase = Showcase::factory()->approved()->for($owner, 'user')->create();
        $draft = ShowcaseDraft::factory()->pending()->create([
            'showcase_id' => $showcase->id,
        ]);

        actingAs($admin);

        post(route('staff.showcase-moderation.drafts.approve', $draft));

        Notification::assertSentTo(
            $owner,
            function (ShowcaseDraftApproved $notification) use ($showcase) {
                return $notification->showcase->id === $showcase->id;
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

        $response = post(route('staff.showcase-moderation.drafts.approve', $draft));

        $response->assertRedirect();
    });

    test('includes success message in session', function () {
        Notification::fake();

        /** @var User */
        $admin = User::factory()->admin()->create();
        $draft = ShowcaseDraft::factory()->pending()->create();

        actingAs($admin);

        $response = post(route('staff.showcase-moderation.drafts.approve', $draft));

        $response->assertSessionHas('flash.message', ['message' => 'Draft changes approved and applied to showcase.', 'type' => 'success']);
    });
});
