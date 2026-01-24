<?php

namespace App\Http\Controllers\Showcase\ManageShowcase;

use App\Enums\SourceStatus;
use App\Http\Controllers\BaseController;
use App\Http\Resources\PracticeAreaResource;
use App\Models\PracticeArea;
use Inertia\Inertia;
use Inertia\Response;

class ShowcaseCreateController extends BaseController
{
    public function __invoke(): Response
    {
        return Inertia::render('showcase/user/create', [
            'practiceAreas' => PracticeAreaResource::collect(PracticeArea::orderBy('name')->get()),
            'sourceStatuses' => collect(SourceStatus::cases())->map(fn (SourceStatus $status) => $status->forFrontend()),
        ]);
    }
}
