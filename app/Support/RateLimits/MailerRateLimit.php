<?php

namespace App\Support\RateLimits;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Config;

class MailerRateLimit
{
    /**
     * @var array{rate_limit_max_attempts:int|string,rate_limit_decay_seconds:int|string}
     */
    protected array $mailerConfig;

    protected string $mailer;

    public function handle(object $job): Limit
    {
        $mailer = $this->resolveMailer($job);

        $this->mailerConfig = Config::get("mail.mailers.{$mailer}", []);

        if ($this->hasRateLimit() === false) {
            return Limit::none();
        }

        return Limit::perSecond(
            maxAttempts: $this->mailerConfig['rate_limit_max_attempts'],
            decaySeconds: $this->mailerConfig['rate_limit_decay_seconds']
        );
    }

    protected function resolveMailer(object $job): string
    {
        if ($job instanceof Mailable && $job->mailer !== null) {
            return $job->mailer;
        }

        if (property_exists($job, 'mailable') && $job->mailable instanceof Mailable && $job->mailable->mailer !== null) {
            return $job->mailable->mailer;
        }

        return Config::get('mail.default');
    }

    protected function hasRateLimit(): bool
    {
        return isset($this->mailerConfig['rate_limit_max_attempts']) && isset($this->mailerConfig['rate_limit_decay_seconds']);
    }
}
