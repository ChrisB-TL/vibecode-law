<?php

namespace App\Services\Content;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\Node\Block\Document;
use League\CommonMark\Normalizer\SlugNormalizer;
use League\CommonMark\Parser\MarkdownParser;

class ContentNavigationService
{
    private const MANIFEST_KEY = 'content-nav:_manifest';

    private ?MarkdownParser $parser = null;

    private ?SlugNormalizer $slugNormalizer = null;

    /**
     * @return array<array{text: string, slug: string, level: int, children: array<mixed>}>
     */
    public function get(string $location): array
    {
        $cacheKey = $this->getCacheKey(location: $location);

        /** @var array<array{text: string, slug: string, level: int, children: array<mixed>}>|null $cached */
        $cached = Cache::get(key: $cacheKey);

        if ($cached !== null) {
            return $cached;
        }

        return $this->build(location: $location);
    }

    /**
     * @return array<HeadingItem>
     */
    public function getHeadings(string $location, int $minLevel = 2, int $maxLevel = 4): array
    {
        $document = $this->parseMarkdown(location: $location);

        return $this->extractHeadings(
            document: $document,
            minLevel: $minLevel,
            maxLevel: $maxLevel
        );
    }

    public function exists(string $location): bool
    {
        return File::exists(path: $this->getFilePath(location: $location));
    }

    public function cache(string $location): void
    {
        Cache::forever(
            key: $this->getCacheKey(location: $location),
            value: $this->build(location: $location)
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
                value: $this->build(location: $location)
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

    public function getCacheKey(string $location): string
    {
        return 'content-nav:'.$location;
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

    /**
     * @return array<array{text: string, slug: string, level: int, children: array<mixed>}>
     */
    private function build(string $location, int $minLevel = 2, int $maxLevel = 4): array
    {
        $headings = $this->getHeadings(
            location: $location,
            minLevel: $minLevel,
            maxLevel: $maxLevel
        );

        if ($headings === []) {
            return [];
        }

        $nested = $this->buildHierarchy(headings: $headings);

        return $this->toArray(headings: $nested);
    }

    private function parseMarkdown(string $location): Document
    {
        $filePath = $this->getFilePath(location: $location);
        $markdown = File::get(path: $filePath);

        return $this->getParser()->parse(input: $markdown);
    }

    /**
     * @return array<HeadingItem>
     */
    private function extractHeadings(Document $document, int $minLevel, int $maxLevel): array
    {
        $headings = [];
        $slugNormalizer = $this->getSlugNormalizer();

        foreach ($document->iterator() as $node) {
            if ($node instanceof Heading === false) {
                continue;
            }

            $level = $node->getLevel();

            if ($level < $minLevel || $level > $maxLevel) {
                continue;
            }

            $text = $this->getHeadingText(heading: $node);
            $slug = $slugNormalizer->normalize($text);

            $headings[] = new HeadingItem(
                text: $text,
                slug: $slug,
                level: $level
            );
        }

        return $headings;
    }

    private function getHeadingText(Heading $heading): string
    {
        $text = '';

        foreach ($heading->children() as $child) {
            if (method_exists($child, 'getLiteral')) {
                $text .= $child->getLiteral();
            }
        }

        return $text;
    }

    /**
     * @param  array<HeadingItem>  $headings
     * @return array<HeadingItem>
     */
    private function buildHierarchy(array $headings): array
    {
        if ($headings === []) {
            return [];
        }

        $index = 0;

        return $this->buildHierarchyRecursive(
            headings: $headings,
            index: $index,
            parentLevel: 0
        );
    }

    /**
     * @param  array<HeadingItem>  $headings
     * @return array<HeadingItem>
     */
    private function buildHierarchyRecursive(array $headings, int &$index, int $parentLevel): array
    {
        $result = [];

        while ($index < count($headings)) {
            $heading = $headings[$index];

            if ($heading->level <= $parentLevel) {
                break;
            }

            $index++;

            $children = $this->buildHierarchyRecursive(
                headings: $headings,
                index: $index,
                parentLevel: $heading->level
            );

            $result[] = new HeadingItem(
                text: $heading->text,
                slug: $heading->slug,
                level: $heading->level,
                children: $children
            );
        }

        return $result;
    }

    /**
     * @param  array<HeadingItem>  $headings
     * @return array<array{text: string, slug: string, level: int, children: array<mixed>}>
     */
    private function toArray(array $headings): array
    {
        return array_map(
            callback: fn (HeadingItem $heading): array => [
                'text' => $heading->text,
                'slug' => $heading->slug,
                'level' => $heading->level,
                'children' => $this->toArray(headings: $heading->children),
            ],
            array: $headings
        );
    }

    private function getParser(): MarkdownParser
    {
        if ($this->parser !== null) {
            return $this->parser;
        }

        $environment = new Environment;
        $environment->addExtension(new CommonMarkCoreExtension);

        $this->parser = new MarkdownParser(environment: $environment);

        return $this->parser;
    }

    private function getSlugNormalizer(): SlugNormalizer
    {
        return $this->slugNormalizer ??= new SlugNormalizer;
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
