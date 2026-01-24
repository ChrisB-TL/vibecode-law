<?php

use App\Services\Content\ContentNavigationService;
use App\Services\Content\HeadingItem;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    Cache::flush();
});

describe('exists', function () {
    it('returns true when content file exists', function () {
        $service = app(abstract: ContentNavigationService::class);

        expect($service->exists(location: 'legal/terms-of-use'))->toBeTrue();
    });

    it('returns false when content file does not exist', function () {
        $service = app(abstract: ContentNavigationService::class);

        expect($service->exists(location: 'nonexistent/file'))->toBeFalse();
    });
});

describe('getHeadings', function () {
    it('returns array of HeadingItem objects', function () {
        $service = app(abstract: ContentNavigationService::class);

        $headings = $service->getHeadings(location: 'legal/terms-of-use');

        expect($headings)->toBeArray();
        expect($headings)->not->toBeEmpty();
        expect($headings[0])->toBeInstanceOf(HeadingItem::class);
    });

    it('extracts text from headings', function () {
        $service = app(abstract: ContentNavigationService::class);

        $headings = $service->getHeadings(location: 'legal/terms-of-use');

        $texts = array_map(fn (HeadingItem $h) => $h->text, $headings);

        expect($texts)->toContain('1. Acceptance of Terms');
    });

    it('generates slugs for headings', function () {
        $service = app(abstract: ContentNavigationService::class);

        $headings = $service->getHeadings(location: 'legal/terms-of-use');

        $firstHeading = $headings[0];

        expect($firstHeading->slug)->toBe('1-acceptance-of-terms');
    });

    it('includes heading levels', function () {
        $service = app(abstract: ContentNavigationService::class);

        $headings = $service->getHeadings(location: 'legal/terms-of-use');

        expect($headings[0]->level)->toBe(2);
    });

    it('excludes h1 headings by default', function () {
        $service = app(abstract: ContentNavigationService::class);

        $headings = $service->getHeadings(location: 'legal/terms-of-use');

        $h1Headings = array_filter(
            $headings,
            fn (HeadingItem $h) => $h->level === 1
        );

        expect($h1Headings)->toBeEmpty();
    });

    it('respects minLevel parameter', function () {
        $service = app(abstract: ContentNavigationService::class);

        $headings = $service->getHeadings(
            location: 'legal/terms-of-use',
            minLevel: 3
        );

        foreach ($headings as $heading) {
            expect($heading->level)->toBeGreaterThanOrEqual(3);
        }
    });

    it('respects maxLevel parameter', function () {
        $service = app(abstract: ContentNavigationService::class);

        $headings = $service->getHeadings(
            location: 'legal/terms-of-use',
            maxLevel: 2
        );

        foreach ($headings as $heading) {
            expect($heading->level)->toBeLessThanOrEqual(2);
        }
    });
});

describe('get', function () {
    it('returns array structure', function () {
        $service = app(abstract: ContentNavigationService::class);

        $navigation = $service->get(location: 'legal/terms-of-use');

        expect($navigation)->toBeArray();
        expect($navigation)->not->toBeEmpty();
    });

    it('returns items with text, slug, level and children keys', function () {
        $service = app(abstract: ContentNavigationService::class);

        $navigation = $service->get(location: 'legal/terms-of-use');

        expect($navigation[0])->toHaveKeys(['text', 'slug', 'level', 'children']);
    });

    it('returns slugs without hash prefix', function () {
        $service = app(abstract: ContentNavigationService::class);

        $navigation = $service->get(location: 'legal/terms-of-use');

        expect($navigation[0]['slug'])->toBe('1-acceptance-of-terms');
        expect($navigation[0]['slug'])->not->toStartWith('#');
    });

    it('creates nested children for sub-headings', function () {
        $service = app(abstract: ContentNavigationService::class);

        $navigation = $service->get(location: 'legal/terms-of-use');

        $accountSection = collect($navigation)->firstWhere('slug', '4-account-registration-and-security');

        expect($accountSection)->not->toBeNull();
        expect($accountSection['children'])->not->toBeEmpty();
        expect($accountSection['children'][0]['slug'])->toBe('41-account-creation');
    });

    it('returns empty array when no headings found', function () {
        $tempFile = base_path('content/test/no-headings.md');
        $tempDir = dirname($tempFile);

        File::makeDirectory(path: $tempDir, recursive: true, force: true);
        File::put(path: $tempFile, contents: "Just some text\n\nNo headings here.");

        try {
            $service = app(abstract: ContentNavigationService::class);

            $navigation = $service->get(location: 'test/no-headings');

            expect($navigation)->toBe([]);
        } finally {
            File::delete(paths: $tempFile);
            File::deleteDirectory(directory: $tempDir);
        }
    });

    it('returns cached content when available', function () {
        $service = app(abstract: ContentNavigationService::class);

        $cachedData = [['text' => 'Cached', 'slug' => 'cached', 'level' => 2, 'children' => []]];
        Cache::forever(key: 'content-nav:legal/terms-of-use', value: $cachedData);

        $result = $service->get(location: 'legal/terms-of-use');

        expect($result)->toBe($cachedData);
    });
});

describe('cache', function () {
    it('caches navigation for a location', function () {
        $service = app(abstract: ContentNavigationService::class);

        $service->cache(location: 'legal/terms-of-use');

        expect(Cache::has(key: 'content-nav:legal/terms-of-use'))->toBeTrue();
    });

    it('caches as array structure', function () {
        $service = app(abstract: ContentNavigationService::class);

        $service->cache(location: 'legal/terms-of-use');

        $cached = Cache::get(key: 'content-nav:legal/terms-of-use');

        expect($cached)->toBeArray();
        expect($cached[0])->toHaveKeys(['text', 'slug', 'level', 'children']);
    });
});

describe('cacheAll', function () {
    it('caches all content files', function () {
        $service = app(abstract: ContentNavigationService::class);

        $count = $service->cacheAll();

        expect($count)->toBeGreaterThanOrEqual(1);
        expect(Cache::has(key: 'content-nav:legal/terms-of-use'))->toBeTrue();
    });

    it('returns count of cached files', function () {
        $service = app(abstract: ContentNavigationService::class);

        $count = $service->cacheAll();

        expect($count)->toBe(count($service->getAllLocations()));
    });
});

describe('clearCache', function () {
    it('clears cache for a specific location', function () {
        $service = app(abstract: ContentNavigationService::class);

        $service->cache(location: 'legal/terms-of-use');
        expect(Cache::has(key: 'content-nav:legal/terms-of-use'))->toBeTrue();

        $result = $service->clearCache(location: 'legal/terms-of-use');

        expect($result)->toBeTrue();
        expect(Cache::has(key: 'content-nav:legal/terms-of-use'))->toBeFalse();
    });

    it('returns false when clearing non-cached location', function () {
        $service = app(abstract: ContentNavigationService::class);

        $result = $service->clearCache(location: 'nonexistent/file');

        expect($result)->toBeFalse();
    });
});

describe('clearAllCache', function () {
    it('clears all cached navigation', function () {
        $service = app(abstract: ContentNavigationService::class);

        $service->cacheAll();
        expect(Cache::has(key: 'content-nav:legal/terms-of-use'))->toBeTrue();

        $service->clearAllCache();

        expect(Cache::has(key: 'content-nav:legal/terms-of-use'))->toBeFalse();
    });

    it('returns count of cleared entries', function () {
        $service = app(abstract: ContentNavigationService::class);

        $service->cacheAll();

        $count = $service->clearAllCache();

        expect($count)->toBeGreaterThanOrEqual(1);
    });
});

describe('getAllLocations', function () {
    it('returns array of content locations', function () {
        $service = app(abstract: ContentNavigationService::class);

        $locations = $service->getAllLocations();

        expect($locations)->toBeArray();
        expect($locations)->toContain('legal/terms-of-use');
    });

    it('returns locations without .md extension', function () {
        $service = app(abstract: ContentNavigationService::class);

        $locations = $service->getAllLocations();

        foreach ($locations as $location) {
            expect($location)->not->toEndWith('.md');
        }
    });
});

describe('getCacheKey', function () {
    it('generates correct cache key format', function () {
        $service = app(abstract: ContentNavigationService::class);

        $key = $service->getCacheKey(location: 'legal/terms-of-use');

        expect($key)->toBe('content-nav:legal/terms-of-use');
    });

    it('generates same key for same location', function () {
        $service = app(abstract: ContentNavigationService::class);

        $key1 = $service->getCacheKey(location: 'legal/terms-of-use');
        $key2 = $service->getCacheKey(location: 'legal/terms-of-use');

        expect($key1)->toBe($key2);
    });

    it('generates different keys for different locations', function () {
        $service = app(abstract: ContentNavigationService::class);

        $key1 = $service->getCacheKey(location: 'legal/terms-of-use');
        $key2 = $service->getCacheKey(location: 'legal/privacy-notice');

        expect($key1)->not->toBe($key2);
    });
});

describe('slug generation', function () {
    it('generates lowercase slugs', function () {
        $service = app(abstract: ContentNavigationService::class);

        $headings = $service->getHeadings(location: 'legal/terms-of-use');

        foreach ($headings as $heading) {
            expect($heading->slug)->toBe(strtolower($heading->slug));
        }
    });

    it('replaces spaces with hyphens', function () {
        $service = app(abstract: ContentNavigationService::class);

        $headings = $service->getHeadings(location: 'legal/terms-of-use');

        foreach ($headings as $heading) {
            expect($heading->slug)->not->toContain(' ');
        }
    });

    it('removes special characters from slugs', function () {
        $service = app(abstract: ContentNavigationService::class);

        $headings = $service->getHeadings(location: 'legal/terms-of-use');

        foreach ($headings as $heading) {
            expect($heading->slug)->toMatch('/^[a-z0-9-]+$/');
        }
    });
});

describe('hierarchy', function () {
    it('nests h3 headings under h2 headings', function () {
        $service = app(abstract: ContentNavigationService::class);

        $navigation = $service->get(location: 'legal/terms-of-use');

        $accountSection = collect($navigation)->firstWhere('slug', '4-account-registration-and-security');

        expect($accountSection['children'])->toHaveCount(2);
        expect($accountSection['children'][0]['slug'])->toBe('41-account-creation');
        expect($accountSection['children'][1]['slug'])->toBe('42-account-security');
    });

    it('creates proper hierarchy for headings at various levels', function () {
        $tempFile = base_path('content/test/hierarchy.md');
        $tempDir = dirname($tempFile);

        $markdown = <<<'MD'
# Title

## Section A

### Sub A1

### Sub A2

## Section B

### Sub B1

#### Deep B1.1

### Sub B2
MD;

        File::makeDirectory(path: $tempDir, recursive: true, force: true);
        File::put(path: $tempFile, contents: $markdown);

        try {
            $service = app(abstract: ContentNavigationService::class);

            $navigation = $service->get(location: 'test/hierarchy');

            expect($navigation)->toHaveCount(2);

            $sectionA = $navigation[0];
            expect($sectionA['slug'])->toBe('section-a');
            expect($sectionA['children'])->toHaveCount(2);

            $sectionB = $navigation[1];
            expect($sectionB['slug'])->toBe('section-b');
            expect($sectionB['children'])->toHaveCount(2);

            $subB1 = $sectionB['children'][0];
            expect($subB1['slug'])->toBe('sub-b1');
            expect($subB1['children'])->toHaveCount(1);
            expect($subB1['children'][0]['slug'])->toBe('deep-b11');
        } finally {
            File::delete(paths: $tempFile);
            File::deleteDirectory(directory: $tempDir);
        }
    });
});
