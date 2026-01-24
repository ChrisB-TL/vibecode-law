<?php

namespace App\Http\Controllers\About;

use App\Http\Controllers\BaseController;
use App\Services\Content\ContentService;
use Illuminate\Support\Facades\Config;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AboutIndexController extends BaseController
{
    public function __construct(
        private ContentService $contentService
    ) {}

    public function __invoke(): Response
    {
        return Inertia::render(component: 'about/index', props: [
            'title' => $this->getIndexTitle(),
            'content' => $this->getContent(),
            'children' => $this->getChildren(),
        ]);
    }

    private function getIndexTitle(): string
    {
        /** @var array{title?: string, slug?: string|null} $config */
        $config = Config::get(key: 'content.about.index', default: []);

        return $config['title'] ?? 'About';
    }

    private function getContent(): string
    {
        $contentPath = 'about/index';

        if ($this->contentService->exists(location: $contentPath) === false) {
            throw new NotFoundHttpException;
        }

        return $this->contentService->get(location: $contentPath);
    }

    /**
     * @return array<int, array{name: string, slug: string, summary: string, icon: string, route: string}>
     */
    private function getChildren(): array
    {
        /** @var array<int, array{title: string, slug: string, summary: string, icon: string, dynamic?: bool}> $childrenConfig */
        $childrenConfig = Config::get(key: 'content.about.children', default: []);

        return collect($childrenConfig)->map(function (array $child): array {
            return [
                'name' => $child['title'],
                'slug' => $child['slug'],
                'summary' => $child['summary'],
                'icon' => $child['icon'],
                'route' => $this->getChildRoute(child: $child),
            ];
        })->all();
    }

    /**
     * @param  array{title: string, slug: string, summary: string, icon: string, dynamic?: bool}  $child
     */
    private function getChildRoute(array $child): string
    {
        $isDynamicCommunity = ($child['dynamic'] ?? false) === true && $child['slug'] === 'the-community';

        return $isDynamicCommunity
            ? route(name: 'about.community')
            : route(name: 'about.show', parameters: ['slug' => $child['slug']]);
    }
}
