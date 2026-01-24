<?php

use App\Enums\ShowcaseStatus;
use App\Models\Showcase\Showcase;
use App\Models\User;
use App\Notifications\Showcase\ShowcaseRejected;
use Illuminate\Support\Facades\Notification;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;

describe('auth', function () {
    test('requires authentication', function () {
        $showcase = Showcase::factory()->pending()->create();

        $response = post(route('staff.showcase-moderation.reject', $showcase), [
            'reason' => 'Test rejection reason',
        ]);

        $response->assertRedirect(route('login'));
    });

    test('requires admin privileges', function () {
        /** @var User */
        $regularUser = User::factory()->create();
        $showcase = Showcase::factory()->pending()->create();

        actingAs($regularUser);

        $response = post(route('staff.showcase-moderation.reject', $showcase), [
            'reason' => 'Test rejection reason',
        ]);

        $response->assertForbidden();
    });

    test('allows admin to reject showcase', function () {
        Notification::fake();

        /** @var User */
        $admin = User::factory()->admin()->create();
        $showcase = Showcase::factory()->pending()->create();

        actingAs($admin);

        $response = post(route('staff.showcase-moderation.reject', $showcase), [
            'reason' => 'Test rejection reason',
        ]);

        $response->assertRedirect();
    });
});

describe('validation', function () {
    test('validates rejection data', function (array $data, array $invalidFields) {
        /** @var User */
        $admin = User::factory()->admin()->create();
        $showcase = Showcase::factory()->pending()->create();

        actingAs($admin);

        $baseData = [
            'reason' => 'Test rejection reason',
        ];

        $response = post(route('staff.showcase-moderation.reject', $showcase), array_merge($baseData, $data));

        $response->assertInvalid($invalidFields);
    })->with([
        'reason is required' => [
            ['reason' => null],
            ['reason'],
        ],
        'reason must be a string' => [
            ['reason' => 123],
            ['reason'],
        ],
        'reason cannot exceed 1000 characters' => [
            ['reason' => str_repeat('a', 1001)],
            ['reason'],
        ],
    ]);
});

describe('rejection', function () {
    test('rejects pending showcase', function () {
        Notification::fake();

        /** @var User */
        $admin = User::factory()->admin()->create();
        $showcase = Showcase::factory()->pending()->create();

        actingAs($admin);

        post(route('staff.showcase-moderation.reject', $showcase), [
            'reason' => 'Does not meet quality standards',
        ]);

        $showcase->refresh();

        expect($showcase->status)->toBe(ShowcaseStatus::Rejected);
        expect($showcase->rejection_reason)->toBe('Does not meet quality standards');
    });

    test('rejects draft showcase', function () {
        Notification::fake();

        /** @var User */
        $admin = User::factory()->admin()->create();
        $showcase = Showcase::factory()->draft()->create();

        actingAs($admin);

        post(route('staff.showcase-moderation.reject', $showcase), [
            'reason' => 'Incomplete information',
        ]);

        $showcase->refresh();

        expect($showcase->status)->toBe(ShowcaseStatus::Rejected);
        expect($showcase->rejection_reason)->toBe('Incomplete information');
    });

    test('rejects approved showcase', function () {
        Notification::fake();

        /** @var User */
        $admin = User::factory()->admin()->create();
        $showcase = Showcase::factory()->approved()->create();

        actingAs($admin);

        post(route('staff.showcase-moderation.reject', $showcase), [
            'reason' => 'Policy violation',
        ]);

        $showcase->refresh();

        expect($showcase->status)->toBe(ShowcaseStatus::Rejected);
        expect($showcase->rejection_reason)->toBe('Policy violation');
    });

    test('clears approval data when rejecting approved showcase', function () {
        Notification::fake();

        /** @var User */
        $admin = User::factory()->admin()->create();
        $approver = User::factory()->admin()->create();
        $showcase = Showcase::factory()->approved()->create([
            'approved_by' => $approver->id,
            'approved_at' => now()->subDays(5),
        ]);

        actingAs($admin);

        post(route('staff.showcase-moderation.reject', $showcase), [
            'reason' => 'Policy violation',
        ]);

        $showcase->refresh();

        expect($showcase->approved_at)->toBeNull();
        expect($showcase->approved_by)->toBeNull();
    });
});

describe('notification', function () {
    test('sends notification to showcase owner', function () {
        Notification::fake();

        /** @var User */
        $admin = User::factory()->admin()->create();
        $owner = User::factory()->create();
        $showcase = Showcase::factory()->pending()->for($owner, 'user')->create();

        actingAs($admin);

        post(route('staff.showcase-moderation.reject', $showcase), [
            'reason' => 'Does not meet quality standards',
        ]);

        Notification::assertSentTo($owner, ShowcaseRejected::class);
    });

    test('notification contains correct showcase', function () {
        Notification::fake();

        /** @var User */
        $admin = User::factory()->admin()->create();
        $owner = User::factory()->create();
        $showcase = Showcase::factory()->pending()->for($owner, 'user')->create();

        actingAs($admin);

        post(route('staff.showcase-moderation.reject', $showcase), [
            'reason' => 'Does not meet quality standards',
        ]);

        Notification::assertSentTo(
            $owner,
            function (ShowcaseRejected $notification) use ($showcase) {
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
        $showcase = Showcase::factory()->pending()->create();

        actingAs($admin);

        $response = post(route('staff.showcase-moderation.reject', $showcase), [
            'reason' => 'Does not meet quality standards',
        ]);

        $response->assertRedirect();
    });

    test('includes success message in session', function () {
        Notification::fake();

        /** @var User */
        $admin = User::factory()->admin()->create();
        $showcase = Showcase::factory()->pending()->create();

        actingAs($admin);

        $response = post(route('staff.showcase-moderation.reject', $showcase), [
            'reason' => 'Does not meet quality standards',
        ]);

        $response->assertSessionHas('flash.message', ['message' => 'Showcase rejected.', 'type' => 'success']);
    });
});
