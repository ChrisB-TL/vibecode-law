<?php

namespace App\Http\Controllers\Staff\ShowcaseModeration;

use App\Enums\ShowcaseDraftStatus;
use App\Enums\ShowcaseStatus;
use App\Http\Controllers\BaseController;
use App\Http\Resources\Showcase\ShowcaseDraftResource;
use App\Http\Resources\Showcase\ShowcaseResource;
use App\Models\Showcase\Showcase;
use App\Models\Showcase\ShowcaseDraft;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class IndexController extends BaseController
{
    public function __invoke(): Response
    {
        return Inertia::render('staff-area/showcase-moderation/index', [
            'pendingShowcases' => ShowcaseResource::collect($this->getPendingShowcases()),
            'rejectedShowcases' => ShowcaseResource::collect($this->getRejectedShowcases()),
            'pendingDrafts' => ShowcaseDraftResource::collect($this->getPendingDrafts()),
            'rejectedDrafts' => ShowcaseDraftResource::collect($this->getRejectedDrafts()),
        ]);
    }

    /**
     * @return Collection<int, Showcase>
     */
    private function getPendingShowcases(): Collection
    {
        return Showcase::query()
            ->with(['user', 'practiceAreas'])
            ->where('status', ShowcaseStatus::Pending)
            ->orderBy('submitted_date', 'asc')
            ->get();
    }

    /**
     * @return Collection<int, Showcase>
     */
    private function getRejectedShowcases(): Collection
    {
        return Showcase::query()
            ->with(['user', 'practiceAreas'])
            ->where('status', ShowcaseStatus::Rejected)
            ->orderBy('updated_at', 'desc')
            ->get();
    }

    /**
     * @return Collection<int, ShowcaseDraft>
     */
    private function getPendingDrafts(): Collection
    {
        return ShowcaseDraft::query()
            ->with(['showcase.user', 'practiceAreas'])
            ->where('status', ShowcaseDraftStatus::Pending)
            ->orderBy('submitted_at', 'asc')
            ->get();
    }

    /**
     * @return Collection<int, ShowcaseDraft>
     */
    private function getRejectedDrafts(): Collection
    {
        return ShowcaseDraft::query()
            ->with(['showcase.user', 'practiceAreas'])
            ->where('status', ShowcaseDraftStatus::Rejected)
            ->orderBy('updated_at', 'desc')
            ->get();
    }
}
