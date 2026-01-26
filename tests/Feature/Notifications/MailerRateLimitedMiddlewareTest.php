<?php

use App\Models\Showcase\Showcase;
use App\Models\User;
use App\Notifications\Showcase\ShowcaseSubmittedForApproval;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;

test('applies rate limit middleware for mail channel', function (): void {
    $user = User::factory()->create();
    $showcase = Showcase::factory()->pending()->create();
    $notification = new ShowcaseSubmittedForApproval($showcase);

    $middleware = $notification->middleware($user, 'mail');

    expect($middleware)->toHaveCount(1)
        ->and($middleware[0])->toBeInstanceOf(RateLimited::class);
});

test('does not apply rate limit middleware for other channels', function (): void {
    $user = User::factory()->create();
    $showcase = Showcase::factory()->pending()->create();
    $notification = new ShowcaseSubmittedForApproval($showcase);

    $middleware = $notification->middleware($user, 'database');

    expect($middleware)->toBeEmpty();
});

test('hits rate limiter when processing mail notification', function (): void {
    Config::set('queue.default', 'sync');
    Config::set('mail.default', 'array');
    Config::set('mail.mailers.array', [
        'transport' => 'array',
        'rate_limit_max_attempts' => 10,
        'rate_limit_decay_seconds' => 60,
    ]);

    $user = User::factory()->create();
    $showcase = Showcase::factory()->pending()->create();

    // The RateLimited middleware uses md5($limiterName . $limit->key) as the cache key
    // Limit::perSecond() uses an empty string as the default key
    $rateLimiterKey = md5('mailer');

    expect(RateLimiter::attempts(key: $rateLimiterKey))->toBe(0);

    $user->notify(new ShowcaseSubmittedForApproval($showcase));

    expect(RateLimiter::attempts(key: $rateLimiterKey))->toBe(1);
});

test('releases job when rate limit is exceeded', function (): void {
    Config::set('queue.default', 'database');
    Config::set('mail.default', 'array');
    Config::set('mail.mailers.array', [
        'transport' => 'array',
        'rate_limit_max_attempts' => 1,
        'rate_limit_decay_seconds' => 60,
    ]);

    $user = User::factory()->create();
    $showcase = Showcase::factory()->pending()->create();

    // Pre-exhaust the rate limit
    $rateLimiterKey = md5('mailer');
    RateLimiter::hit(key: $rateLimiterKey, decaySeconds: 60);

    expect(RateLimiter::tooManyAttempts(key: $rateLimiterKey, maxAttempts: 1))->toBeTrue();

    expect(DB::table('jobs')->count())->toBe(0);

    // Dispatch the notification
    $user->notify(new ShowcaseSubmittedForApproval($showcase));

    // Job should be queued
    expect(DB::table('jobs')->count())->toBe(1);

    // Process the job - it should be released back due to rate limit
    Artisan::call('queue:work', ['--once' => true, '--sleep' => 0]);

    // Job should still exist (released back to queue with delay)
    // The available_at timestamp will be in the future
    $job = DB::table('jobs')->first();
    expect($job)->not->toBeNull()
        ->and($job->available_at)->toBeGreaterThan(now()->timestamp);
});
