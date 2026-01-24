<?php

namespace App\Http\Controllers\About;

use App\Http\Controllers\BaseController;
use App\Services\Content\ContentNavigationService;
use App\Services\Content\ContentService;
use Illuminate\Support\Facades\Config;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AboutShowController extends BaseController
{
    public function __construct(
        private ContentService $contentService,
        private ContentNavigationService $contentNavigationService
    ) {}

    public function __invoke(string $slug): Response
    {
        $contentPath = "about/{$slug}";

        return Inertia::render(component: 'about/show', props: [
            'title' => $this->getTitle(slug: $slug),
            'slug' => $slug,
            'content' => $this->getContent(contentPath: $contentPath),
            'navigation' => $this->contentNavigationService->get(location: $contentPath),
        ]);
    }

    private function getTitle(string $slug): string
    {
        /** @var array<int, array{title: string, slug: string}> $childrenConfig */
        $childrenConfig = Config::get(key: 'content.about.children', default: []);

        $page = collect($childrenConfig)->firstWhere('slug', $slug);

        if ($page === null) {
            throw new NotFoundHttpException;
        }

        return $page['title'] ?? 'About';
    }

    private function getContent(string $contentPath): string
    {
        if ($this->contentService->exists(location: $contentPath) === false) {
            throw new NotFoundHttpException;
        }

        return $this->contentService->get(location: $contentPath);
    }
}
