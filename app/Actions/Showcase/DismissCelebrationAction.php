<?php

namespace App\Actions\Showcase;

use App\Models\Showcase\Showcase;
use Illuminate\Support\Facades\Date;

class DismissCelebrationAction
{
    public function dismiss(Showcase $showcase): void
    {
        $showcase->update([
            'approval_celebrated_at' => Date::now(),
        ]);
    }
}
