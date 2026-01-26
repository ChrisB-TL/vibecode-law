<?php

use App\Support\RateLimits\MailerRateLimit;
use Illuminate\Support\Facades\Config;

test('returns configured rate limit when mailer has rate limit config', function (): void {
    Config::set('mail.default', 'resend');
    Config::set('mail.mailers.resend', [
        'rate_limit_max_attempts' => 10,
        'rate_limit_decay_seconds' => 60,
    ]);

    $job = new stdClass;
    $limit = (new MailerRateLimit)->handle($job);

    expect($limit->maxAttempts)->toBe(10)
        ->and($limit->decaySeconds)->toBe(60);
});

test('returns no limit when mailer lacks rate limit config', function (): void {
    Config::set('mail.default', 'resend');
    Config::set('mail.mailers.resend', []);

    $job = new stdClass;
    $limit = (new MailerRateLimit)->handle($job);

    // Limit::none() returns PHP_INT_MAX for maxAttempts
    expect($limit->maxAttempts)->toBe(PHP_INT_MAX);
});
