<?php

namespace App\Http\Controllers\Showcase\ManageShowcaseDraft;

use App\Actions\ShowcaseDraft\DiscardShowcaseDraftAction;
use App\Http\Controllers\BaseController;
use App\Models\Showcase\ShowcaseDraft;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class ShowcaseDraftDestroyController extends BaseController
{
    public function __invoke(ShowcaseDraft $draft, DiscardShowcaseDraftAction $action): RedirectResponse
    {
        $this->authorize('delete', $draft);

        $action->discard(draft: $draft);

        return Redirect::route('user-area.showcases.index')->with('flash', [
            'message' => ['message' => 'Draft discarded.', 'type' => 'success'],
        ]);
    }
}
