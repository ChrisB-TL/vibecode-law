<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\BaseController;
use App\Http\Resources\Showcase\ShowcaseResource;
use App\Models\Showcase\Showcase;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\LaravelData\DataCollection;

class UserShowcaseIndexController extends BaseController
{
    public function __invoke(Request $request): Response
    {
        $showcases = Showcase::query()
            ->with(['draft' => function (Relation $query): void {
                $query->select(['id', 'status', 'showcase_id']);
            }])
            ->withCount('upvoters')
            ->whereBelongsTo($request->user(), 'user')
            ->latest()
            ->get();

        return Inertia::render('user-area/showcases', [
            'showcases' => ShowcaseResource::collect($showcases, DataCollection::class)
                ->only(
                    'id',
                    'slug',
                    'title',
                    'tagline',
                    'status',
                    'thumbnail_url',
                    'thumbnail_rect_string',
                    'view_count',
                    'upvotes_count',
                    'rejection_reason',
                    'has_draft',
                    'draft_id',
                    'draft_status',
                ),
        ]);
    }
}
