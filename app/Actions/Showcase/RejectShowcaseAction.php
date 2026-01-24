<?php

namespace App\Actions\Showcase;

use App\Enums\ShowcaseStatus;
use App\Models\Showcase\Showcase;
use App\Models\User;

class RejectShowcaseAction
{
    public function reject(Showcase $showcase, User $user, string $reason): void
    {
        $showcase->update([
            'status' => ShowcaseStatus::Rejected,
            'approved_at' => null,
            'approved_by' => null,
            'rejection_reason' => $reason,
        ]);
    }
}
