<?php

namespace App\Http\Controllers\Showcase\ManageShowcaseDraft;

use App\Enums\SourceStatus;
use App\Http\Controllers\BaseController;
use App\Http\Resources\PracticeAreaResource;
use App\Http\Resources\Showcase\ShowcaseDraftResource;
use App\Models\PracticeArea;
use App\Models\Showcase\ShowcaseDraft;
use Inertia\Inertia;
use Inertia\Response;

class ShowcaseDraftEditController extends BaseController
{
    public function __invoke(ShowcaseDraft $draft): Response
    {
        $this->authorize('update', $draft);

        $draft->load(['images.originalImage', 'practiceAreas', 'showcase']);

        return Inertia::render('showcase/user/edit-draft', [
            'draft' => ShowcaseDraftResource::from($draft)->include('thumbnail_crop'),
            'practiceAreas' => PracticeAreaResource::collect(PracticeArea::orderBy('name')->get()),
            'sourceStatuses' => collect(SourceStatus::cases())->map(fn (SourceStatus $status) => $status->forFrontend()),
        ]);
    }
}
