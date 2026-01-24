<?php

namespace App\Http\Controllers\Staff\ShowcaseModeration;

use App\Actions\ShowcaseDraft\RejectShowcaseDraftAction;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Showcase\ShowcaseDraftRejectRequest;
use App\Models\Showcase\ShowcaseDraft;
use App\Notifications\ShowcaseDraft\ShowcaseDraftRejected;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class RejectDraftController extends BaseController
{
    public function __invoke(ShowcaseDraftRejectRequest $request, ShowcaseDraft $draft, RejectShowcaseDraftAction $action): RedirectResponse
    {
        $this->authorize('moderate', $draft);

        /** @var string $reason */
        $reason = $request->validated('reason');

        $action->reject(draft: $draft, reason: $reason);

        $draft->showcase->user->notify(new ShowcaseDraftRejected($draft, $reason));

        return Redirect::route('staff.showcase-moderation.index')->with('flash', [
            'message' => ['message' => 'Draft rejected.', 'type' => 'success'],
        ]);
    }
}
