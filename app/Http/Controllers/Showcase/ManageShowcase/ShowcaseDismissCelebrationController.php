<?php

namespace App\Http\Controllers\Showcase\ManageShowcase;

use App\Actions\Showcase\DismissCelebrationAction;
use App\Http\Controllers\BaseController;
use App\Models\Showcase\Showcase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class ShowcaseDismissCelebrationController extends BaseController
{
    public function __invoke(Request $request, Showcase $showcase): RedirectResponse
    {
        if ($request->user()->id !== $showcase->user_id) {
            abort(403);
        }

        (new DismissCelebrationAction)->dismiss(showcase: $showcase);

        return Redirect::back();
    }
}
