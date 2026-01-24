<?php

use App\Services\Content\ContentNavigationService;
use App\Services\Content\ContentService;
use Illuminate\Support\Facades\Cache;

use function Pest\Laravel\artisan;

beforeEach(function () {
    Cache::flush();
});

describe('app:content:clear', function () {
    it('clears all content cache', function () {
        app(abstract: ContentService::class)->cache(location: 'legal/terms-of-use');
        expect(Cache::has(key: 'content:legal/terms-of-use'))->toBeTrue();

        artisan(command: 'app:content:clear')
            ->assertSuccessful()
            ->expectsOutputToContain('Cleared');

        expect(Cache::has(key: 'content:legal/terms-of-use'))->toBeFalse();
    });

    it('clears all navigation cache', function () {
        app(abstract: ContentNavigationService::class)->cache(location: 'legal/terms-of-use');
        expect(Cache::has(key: 'content-nav:legal/terms-of-use'))->toBeTrue();

        artisan(command: 'app:content:clear')
            ->assertSuccessful();

        expect(Cache::has(key: 'content-nav:legal/terms-of-use'))->toBeFalse();
    });

    it('clears specific location when option provided', function () {
        app(abstract: ContentService::class)->cache(location: 'legal/terms-of-use');
        app(abstract: ContentNavigationService::class)->cache(location: 'legal/terms-of-use');

        artisan(command: 'app:content:clear', parameters: ['--location' => 'legal/terms-of-use'])
            ->assertSuccessful()
            ->expectsOutputToContain('Cleared cache for: legal/terms-of-use');

        expect(Cache::has(key: 'content:legal/terms-of-use'))->toBeFalse();
        expect(Cache::has(key: 'content-nav:legal/terms-of-use'))->toBeFalse();
    });

    it('warns when no cache exists for specific location', function () {
        artisan(command: 'app:content:clear', parameters: ['--location' => 'nonexistent/file'])
            ->assertSuccessful()
            ->expectsOutputToContain('No cache found for: nonexistent/file');
    });

    it('outputs count of cleared entries for both content and navigation', function () {
        app(abstract: ContentService::class)->cache(location: 'legal/terms-of-use');
        app(abstract: ContentNavigationService::class)->cache(location: 'legal/terms-of-use');

        artisan(command: 'app:content:clear')
            ->assertSuccessful()
            ->expectsOutputToContain('content cache(s) and 1 navigation cache(s)');
    });

    it('returns success even when nothing to clear', function () {
        artisan(command: 'app:content:clear')
            ->assertSuccessful()
            ->expectsOutputToContain('Cleared 0 content cache(s) and 0 navigation cache(s)');
    });
});
