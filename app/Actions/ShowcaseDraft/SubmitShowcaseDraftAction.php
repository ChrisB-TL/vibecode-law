<?php

namespace App\Actions\ShowcaseDraft;

use App\Enums\ShowcaseDraftStatus;
use App\Models\Showcase\ShowcaseDraft;

class SubmitShowcaseDraftAction
{
    public function submit(ShowcaseDraft $draft): void
    {
        $draft->update([
            'status' => ShowcaseDraftStatus::Pending,
            'submitted_at' => now(),
            'rejection_reason' => null,
        ]);
    }
}
