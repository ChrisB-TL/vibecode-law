<?php

namespace App\Concerns;

use Illuminate\Queue\Middleware\RateLimited;

trait ThrottlesMail
{
    /**
     * @return array<int,object>
     */
    public function middleware(object $notifiable, string $channel): array
    {
        if ($channel !== 'mail') {
            return [];
        }

        return [new RateLimited('mailer')];
    }
}
