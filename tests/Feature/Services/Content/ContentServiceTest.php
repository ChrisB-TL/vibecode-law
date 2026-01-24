<?php

use App\Services\Content\ContentService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

beforeEach(function () {
    Cache::flush();
});

describe('exists', function () {
    it('returns true when content file exists', function () {
        $service = app(abstract: ContentService::class);

        expect($service->exists(location: 'legal/terms-of-use'))->toBeTrue();
    });

    it('returns false when content file does not exist', function () {
        $service = app(abstract: ContentService::class);

        expect($service->exists(location: 'nonexistent/file'))->toBeFalse();
    });
});

describe('get', function () {
    it('returns rendered html for existing content', function () {
        $service = app(abstract: ContentService::class);

        $html = $service->get(location: 'legal/terms-of-use');

        expect($html)->toMatch('/terms of use/i');
    });

    it('does not cache content on retrieval', function () {
        $service = app(abstract: ContentService::class);

        $service->get(location: 'legal/terms-of-use');

        expect(Cache::has(key: 'content:legal/terms-of-use'))->toBeFalse();
    });

    it('returns cached content when available', function () {
        $service = app(abstract: ContentService::class);

        Cache::forever(key: 'content:legal/terms-of-use', value: '<p>Cached</p>');

        $result = $service->get(location: 'legal/terms-of-use');

        expect($result)->toBe('<p>Cached</p>');
    });
});

describe('cache', function () {
    it('caches content for a location', function () {
        $service = app(abstract: ContentService::class);

        $service->cache(location: 'legal/terms-of-use');

        expect(Cache::has(key: 'content:legal/terms-of-use'))->toBeTrue();
    });

    it('overwrites existing cache for a location', function () {
        $service = app(abstract: ContentService::class);

        Cache::forever(key: 'content:legal/terms-of-use', value: 'old content');

        $service->cache(location: 'legal/terms-of-use');

        $cachedContent = Cache::get(key: 'content:legal/terms-of-use');
        expect($cachedContent)->not->toBe('old content');
        expect($cachedContent)->toMatch('/terms of use/i');
    });
});

describe('cacheAll', function () {
    it('caches all content files', function () {
        $service = app(abstract: ContentService::class);

        $count = $service->cacheAll();

        expect($count)->toBeGreaterThanOrEqual(1);
        expect(Cache::has(key: 'content:legal/terms-of-use'))->toBeTrue();
    });

    it('returns count of cached files', function () {
        $service = app(abstract: ContentService::class);

        $count = $service->cacheAll();

        expect($count)->toBe(count($service->getAllLocations()));
    });
});

describe('clearCache', function () {
    it('clears cache for a specific location', function () {
        $service = app(abstract: ContentService::class);

        $service->cache(location: 'legal/terms-of-use');
        expect(Cache::has(key: 'content:legal/terms-of-use'))->toBeTrue();

        $result = $service->clearCache(location: 'legal/terms-of-use');

        expect($result)->toBeTrue();
        expect(Cache::has(key: 'content:legal/terms-of-use'))->toBeFalse();
    });

    it('returns false when clearing non-cached location', function () {
        $service = app(abstract: ContentService::class);

        $result = $service->clearCache(location: 'nonexistent/file');

        expect($result)->toBeFalse();
    });
});

describe('clearAllCache', function () {
    it('clears all cached content', function () {
        $service = app(abstract: ContentService::class);

        $service->cacheAll();
        expect(Cache::has(key: 'content:legal/terms-of-use'))->toBeTrue();

        $service->clearAllCache();

        expect(Cache::has(key: 'content:legal/terms-of-use'))->toBeFalse();
    });

    it('returns count of cleared entries', function () {
        $service = app(abstract: ContentService::class);

        $service->cacheAll();

        $count = $service->clearAllCache();

        expect($count)->toBeGreaterThanOrEqual(1);
    });

    it('clears stale cache entries for deleted files', function () {
        $service = app(abstract: ContentService::class);

        $service->cache(location: 'legal/terms-of-use');
        Cache::forever(key: 'content:deleted/file', value: 'stale');
        Cache::forever(key: 'content:_manifest', value: ['legal/terms-of-use', 'deleted/file']);

        $count = $service->clearAllCache();

        expect($count)->toBe(2);
        expect(Cache::has(key: 'content:legal/terms-of-use'))->toBeFalse();
        expect(Cache::has(key: 'content:deleted/file'))->toBeFalse();
    });
});

describe('getAllLocations', function () {
    it('returns array of content locations', function () {
        $service = app(abstract: ContentService::class);

        $locations = $service->getAllLocations();

        expect($locations)->toBeArray();
        expect($locations)->toContain('legal/terms-of-use');
    });

    it('returns locations without .md extension', function () {
        $service = app(abstract: ContentService::class);

        $locations = $service->getAllLocations();

        foreach ($locations as $location) {
            expect($location)->not->toEndWith('.md');
        }
    });

    it('returns empty array when content directory does not exist', function () {
        $contentPath = base_path('content');
        $tempPath = base_path('content_temp');

        File::moveDirectory(from: $contentPath, to: $tempPath);

        try {
            $service = app(abstract: ContentService::class);
            $locations = $service->getAllLocations();

            expect($locations)->toBe([]);
        } finally {
            File::moveDirectory(from: $tempPath, to: $contentPath);
        }
    });
});

describe('getCacheKey', function () {
    it('generates correct cache key format', function () {
        $service = app(abstract: ContentService::class);

        $key = $service->getCacheKey(location: 'legal/terms-of-use');

        expect($key)->toBe('content:legal/terms-of-use');
    });

    it('generates same key for same location', function () {
        $service = app(abstract: ContentService::class);

        $key1 = $service->getCacheKey(location: 'legal/terms-of-use');
        $key2 = $service->getCacheKey(location: 'legal/terms-of-use');

        expect($key1)->toBe($key2);
    });

    it('generates different keys for different locations', function () {
        $service = app(abstract: ContentService::class);

        $key1 = $service->getCacheKey(location: 'legal/terms-of-use');
        $key2 = $service->getCacheKey(location: 'legal/privacy-policy');

        expect($key1)->not->toBe($key2);
    });
});

describe('markdown rendering', function () {
    it('renders markdown using full profile', function () {
        $service = app(abstract: ContentService::class);

        $html = $service->get(location: 'legal/terms-of-use');

        expect($html)->toContain('<h1>');
        expect($html)->toContain('<h2 id=');
        expect($html)->toContain('<p>');
    });
});
