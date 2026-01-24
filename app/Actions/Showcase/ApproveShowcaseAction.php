<?php

namespace App\Actions\Showcase;

use App\Enums\ShowcaseStatus;
use App\Models\Showcase\Showcase;
use App\Models\User;

class ApproveShowcaseAction
{
    public function approve(Showcase $showcase, User $user): void
    {
        $showcase->update([
            'status' => ShowcaseStatus::Approved,
            'approved_at' => now(),
            'approved_by' => $user->id,
            'rejection_reason' => null,
        ]);
    }
}
