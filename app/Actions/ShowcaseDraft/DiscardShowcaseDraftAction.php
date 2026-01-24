<?php

namespace App\Actions\ShowcaseDraft;

use App\Models\Showcase\ShowcaseDraft;

class DiscardShowcaseDraftAction
{
    public function discard(ShowcaseDraft $draft): void
    {
        // The model's deleted event handles cleanup of draft files
        $draft->delete();
    }
}
