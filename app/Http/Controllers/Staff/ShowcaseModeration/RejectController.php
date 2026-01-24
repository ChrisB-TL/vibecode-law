<?php

namespace App\Http\Controllers\Staff\ShowcaseModeration;

use App\Actions\Showcase\RejectShowcaseAction;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Showcase\ShowcaseRejectRequest;
use App\Models\Showcase\Showcase;
use App\Notifications\Showcase\ShowcaseRejected;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class RejectController extends BaseController
{
    public function __invoke(ShowcaseRejectRequest $request, Showcase $showcase): RedirectResponse
    {
        $this->authorize('toggleApproval', $showcase);

        new RejectShowcaseAction()->reject(
            showcase: $showcase,
            user: $request->user(),
            reason: $request->validated('reason')
        );

        $showcase->user->notify(new ShowcaseRejected($showcase));

        return Redirect::route('staff.showcase-moderation.index')->with('flash', [
            'message' => ['message' => 'Showcase rejected.', 'type' => 'success'],
        ]);
    }
}
