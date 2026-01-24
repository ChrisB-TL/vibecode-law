<?php

namespace App\Actions\ShowcaseDraft;

use App\Enums\ShowcaseDraftStatus;
use App\Models\Showcase\ShowcaseDraft;

class RejectShowcaseDraftAction
{
    public function reject(ShowcaseDraft $draft, string $reason): void
    {
        $draft->update([
            'status' => ShowcaseDraftStatus::Rejected,
            'rejection_reason' => $reason,
        ]);
    }
}
