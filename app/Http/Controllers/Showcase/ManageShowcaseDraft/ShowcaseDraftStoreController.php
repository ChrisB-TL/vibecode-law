<?php

namespace App\Http\Controllers\Showcase\ManageShowcaseDraft;

use App\Actions\ShowcaseDraft\CreateShowcaseDraftAction;
use App\Http\Controllers\BaseController;
use App\Models\Showcase\Showcase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

class ShowcaseDraftStoreController extends BaseController
{
    public function __invoke(Showcase $showcase, CreateShowcaseDraftAction $action): RedirectResponse
    {
        $this->authorize('createDraft', $showcase);

        // Check if a draft already exists for this showcase
        if ($showcase->draft !== null) {
            return Redirect::route('showcase.draft.edit', $showcase->draft)->with('flash', [
                'message' => ['message' => 'A draft already exists for this showcase.', 'type' => 'info'],
            ]);
        }

        $draft = $action->create(showcase: $showcase);

        return Redirect::route('showcase.draft.edit', $draft)->with('flash', [
            'message' => ['message' => 'Draft created. You can now make changes.', 'type' => 'success'],
        ]);
    }
}
