<?php

namespace App\Http\Controllers\Staff\ShowcaseModeration;

use App\Http\Controllers\BaseController;
use App\Models\Showcase\Showcase;
use Illuminate\Http\RedirectResponse;

class ToggleFeaturedController extends BaseController
{
    public function __invoke(Showcase $showcase): RedirectResponse
    {
        $this->authorize('toggleFeatured', $showcase);

        if ($showcase->isApproved() === false) {
            return $this->errorResponse();
        }

        $showcase->update(['is_featured' => ! $showcase->is_featured]);

        return $this->successResponse(showcase: $showcase);
    }

    private function errorResponse(): RedirectResponse
    {
        return back()->with('flash', [
            'message' => ['message' => 'Only approved showcases can be featured.', 'type' => 'error'],
        ]);
    }

    private function successResponse(Showcase $showcase): RedirectResponse
    {
        return back()->with('flash', [
            'message' => [
                'message' => $showcase->is_featured ? 'Showcase featured.' : 'Showcase unfeatured.',
                'type' => 'success',
            ],
        ]);
    }
}
