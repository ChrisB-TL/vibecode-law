<?php

use App\Actions\Showcase\SubmitShowcaseAction;
use App\Enums\ShowcaseStatus;
use App\Models\Showcase\Showcase;
use App\Models\User;
use App\Notifications\Showcase\ShowcaseSubmittedForApproval;
use Illuminate\Support\Facades\Notification;

describe('showcase submission', function () {
    test('updates status to pending', function () {
        Notification::fake();

        $showcase = Showcase::factory()->draft()->create();

        (new SubmitShowcaseAction)->submit(showcase: $showcase);

        expect($showcase->fresh()->status)->toBe(ShowcaseStatus::Pending);
    });

    test('sets submitted_date timestamp', function () {
        Notification::fake();

        $showcase = Showcase::factory()->draft()->create();

        expect($showcase->submitted_date)->toBeNull();

        (new SubmitShowcaseAction)->submit(showcase: $showcase);

        expect($showcase->fresh()->submitted_date)->not->toBeNull();
    });
});

describe('notifications', function () {
    test('sends notification to admins', function () {
        Notification::fake();

        /** @var User */
        $admin = User::factory()->admin()->create();
        $showcase = Showcase::factory()->draft()->create();

        (new SubmitShowcaseAction)->submit(showcase: $showcase);

        Notification::assertSentTo($admin, ShowcaseSubmittedForApproval::class);
    });

    test('sends notification to moderators', function () {
        Notification::fake();

        /** @var User */
        $moderator = User::factory()->moderator()->create();
        $showcase = Showcase::factory()->draft()->create();

        (new SubmitShowcaseAction)->submit(showcase: $showcase);

        Notification::assertSentTo($moderator, ShowcaseSubmittedForApproval::class);
    });

    test('sends notification to all admins and moderators', function () {
        Notification::fake();

        /** @var User */
        $admin1 = User::factory()->admin()->create();
        /** @var User */
        $admin2 = User::factory()->admin()->create();
        /** @var User */
        $moderator = User::factory()->moderator()->create();
        $showcase = Showcase::factory()->draft()->create();

        (new SubmitShowcaseAction)->submit(showcase: $showcase);

        Notification::assertSentTo($admin1, ShowcaseSubmittedForApproval::class);
        Notification::assertSentTo($admin2, ShowcaseSubmittedForApproval::class);
        Notification::assertSentTo($moderator, ShowcaseSubmittedForApproval::class);
    });

    test('does not send notification to regular users', function () {
        Notification::fake();

        /** @var User */
        $regularUser = User::factory()->create();
        $showcase = Showcase::factory()->draft()->create();

        (new SubmitShowcaseAction)->submit(showcase: $showcase);

        Notification::assertNotSentTo($regularUser, ShowcaseSubmittedForApproval::class);
    });

    test('notification contains correct showcase', function () {
        Notification::fake();

        /** @var User */
        $admin = User::factory()->admin()->create();
        $showcase = Showcase::factory()->draft()->create();

        (new SubmitShowcaseAction)->submit(showcase: $showcase);

        Notification::assertSentTo(
            $admin,
            function (ShowcaseSubmittedForApproval $notification) use ($showcase) {
                return $notification->showcase->id === $showcase->id;
            }
        );
    });

    test('only sends one notification per staff member even if both admin and moderator', function () {
        Notification::fake();

        /** @var User */
        $adminModerator = User::factory()->admin()->moderator()->create();
        $showcase = Showcase::factory()->draft()->create();

        (new SubmitShowcaseAction)->submit(showcase: $showcase);

        Notification::assertSentToTimes($adminModerator, ShowcaseSubmittedForApproval::class, times: 1);
    });
});
