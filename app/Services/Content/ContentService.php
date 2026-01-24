<?php

namespace App\Services\Content;

use App\Enums\MarkdownProfile;
use App\Services\Markdown\MarkdownService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class ContentService
{
    private const MANIFEST_KEY = 'content:_manifest';

    public function __construct(
        private MarkdownService $markdownService
    ) {}

    public function get(string $location): string
    {
        $cacheKey = $this->getCacheKey(location: $location);

        $cached = Cache::get(key: $cacheKey);

        if ($cached !== null) {
            return $cached;
        }

        return $this->render(location: $location);
    }

    public function exists(string $location): bool
    {
        return File::exists(path: $this->getFilePath(location: $location));
    }

    public function cache(string $location): void
    {
        Cache::forever(
            key: $this->getCacheKey(location: $location),
            value: $this->render(location: $location)
        );

        $this->addToManifest(location: $location);
    }

    /**
     * @return int The number of files cached
     */
    public function cacheAll(): int
    {
        $this->clearAllCache();

        $locations = $this->getAllLocations();

        foreach ($locations as $location) {
            Cache::forever(
                key: $this->getCacheKey(location: $location),
                value: $this->render(location: $location)
            );
        }

        $this->setManifest(locations: $locations);

        return count($locations);
    }

    public function clearCache(string $location): bool
    {
        $this->removeFromManifest(location: $location);

        return Cache::forget(key: $this->getCacheKey(location: $location));
    }

    /**
     * @return int The number of cache entries cleared
     */
    public function clearAllCache(): int
    {
        $cachedLocations = $this->getManifest();

        $count = 0;

        foreach ($cachedLocations as $location) {
            if (Cache::forget(key: $this->getCacheKey(location: $location)) === true) {
                $count++;
            }
        }

        Cache::forget(key: self::MANIFEST_KEY);

        return $count;
    }

    /**
     * @return array<string>
     */
    public function getAllLocations(): array
    {
        $contentPath = $this->getContentBasePath();

        if (File::isDirectory(directory: $contentPath) === false) {
            return [];
        }

        $files = File::allFiles(directory: $contentPath);
        $locations = [];

        foreach ($files as $file) {
            if ($file->getExtension() === 'md') {
                $relativePath = $file->getRelativePathname();
                $locations[] = preg_replace('/\.md$/', '', $relativePath);
            }
        }

        return $locations;
    }

    public function getCacheKey(string $location): string
    {
        return 'content:'.$location;
    }

    /**
     * @return array<string>
     */
    private function getManifest(): array
    {
        return Cache::get(key: self::MANIFEST_KEY, default: []);
    }

    /**
     * @param  array<string>  $locations
     */
    private function setManifest(array $locations): void
    {
        Cache::forever(key: self::MANIFEST_KEY, value: $locations);
    }

    private function addToManifest(string $location): void
    {
        $manifest = $this->getManifest();

        if (in_array(needle: $location, haystack: $manifest, strict: true) === false) {
            $manifest[] = $location;
            $this->setManifest(locations: $manifest);
        }
    }

    private function removeFromManifest(string $location): void
    {
        $manifest = $this->getManifest();
        $manifest = array_values(array_filter(
            array: $manifest,
            callback: fn (string $loc): bool => $loc !== $location
        ));
        $this->setManifest(locations: $manifest);
    }

    private function render(string $location): string
    {
        $filePath = $this->getFilePath(location: $location);
        $markdown = File::get(path: $filePath);

        return $this->markdownService->renderWithoutCache(markdown: $markdown, profile: MarkdownProfile::Full);
    }

    private function getFilePath(string $location): string
    {
        return $this->getContentBasePath().'/'.$location.'.md';
    }

    private function getContentBasePath(): string
    {
        return base_path('content');
    }
}
