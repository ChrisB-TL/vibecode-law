<?php

use Illuminate\Support\Facades\Cache;

use function Pest\Laravel\artisan;

beforeEach(function () {
    Cache::flush();
});

describe('app:content:cache', function () {
    it('caches all content files', function () {
        artisan(command: 'app:content:cache')
            ->assertSuccessful()
            ->expectsOutputToContain('Cached');

        expect(Cache::has(key: 'content:legal/terms-of-use'))->toBeTrue();
    });

    it('caches navigation for all content files', function () {
        artisan(command: 'app:content:cache')
            ->assertSuccessful();

        expect(Cache::has(key: 'content-nav:legal/terms-of-use'))->toBeTrue();
    });

    it('clears and rebuilds cache', function () {
        Cache::forever(key: 'content:legal/terms-of-use', value: 'old content');

        artisan(command: 'app:content:cache')
            ->assertSuccessful();

        $cachedContent = Cache::get(key: 'content:legal/terms-of-use');
        expect($cachedContent)->not->toBe('old content');
        expect($cachedContent)->toMatch('/terms of use/i');
    });

    it('caches specific location when option provided', function () {
        artisan(command: 'app:content:cache', parameters: ['--location' => 'legal/terms-of-use'])
            ->assertSuccessful()
            ->expectsOutputToContain('Cached content and navigation: legal/terms-of-use');

        expect(Cache::has(key: 'content:legal/terms-of-use'))->toBeTrue();
        expect(Cache::has(key: 'content-nav:legal/terms-of-use'))->toBeTrue();
    });

    it('fails when location does not exist', function () {
        artisan(command: 'app:content:cache', parameters: ['--location' => 'nonexistent/file'])
            ->assertFailed()
            ->expectsOutputToContain('Content not found: nonexistent/file');
    });

    it('outputs count of cached files with navigation', function () {
        artisan(command: 'app:content:cache')
            ->assertSuccessful()
            ->expectsOutputToContain('content file(s) with navigation');
    });
});
