<?php

namespace App\Http\Controllers\Staff\ShowcaseModeration;

use App\Actions\Showcase\ApproveShowcaseAction;
use App\Http\Controllers\BaseController;
use App\Models\Showcase\Showcase;
use App\Notifications\Showcase\ShowcaseApproved;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class ApproveController extends BaseController
{
    public function __invoke(Request $request, Showcase $showcase): RedirectResponse
    {
        $this->authorize('toggleApproval', $showcase);

        new ApproveShowcaseAction()->approve(
            showcase: $showcase,
            user: $request->user()
        );

        $showcase->user->notify(new ShowcaseApproved($showcase));

        return Redirect::route('staff.showcase-moderation.index')->with('flash', [
            'message' => ['message' => 'Showcase approved successfully.', 'type' => 'success'],
        ]);
    }
}
