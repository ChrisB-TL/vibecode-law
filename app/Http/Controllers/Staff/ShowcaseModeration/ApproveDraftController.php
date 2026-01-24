<?php

namespace App\Http\Controllers\Staff\ShowcaseModeration;

use App\Actions\ShowcaseDraft\ApproveShowcaseDraftAction;
use App\Http\Controllers\BaseController;
use App\Models\Showcase\ShowcaseDraft;
use App\Notifications\ShowcaseDraft\ShowcaseDraftApproved;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class ApproveDraftController extends BaseController
{
    public function __invoke(ShowcaseDraft $draft, ApproveShowcaseDraftAction $action): RedirectResponse
    {
        $this->authorize('moderate', $draft);

        $showcase = $action->approve(draft: $draft);

        $showcase->user->notify(new ShowcaseDraftApproved($showcase));

        return Redirect::route('staff.showcase-moderation.index')->with('flash', [
            'message' => ['message' => 'Draft changes approved and applied to showcase.', 'type' => 'success'],
        ]);
    }
}
