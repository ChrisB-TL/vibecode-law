<?php

namespace App\Console\Commands;

use App\Services\Content\ContentNavigationService;
use App\Services\Content\ContentService;
use Illuminate\Console\Command;

class CacheContentCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'app:content:cache {--location= : Cache a specific location only}';

    /**
     * @var string
     */
    protected $description = 'Cache content files';

    public function handle(
        ContentService $contentService,
        ContentNavigationService $contentNavigationService
    ): int {
        $location = $this->option(key: 'location');

        if ($location !== null) {
            if ($contentService->exists(location: $location) === false) {
                $this->components->error(string: "Content not found: {$location}");

                return Command::FAILURE;
            }

            $contentService->cache(location: $location);
            $contentNavigationService->cache(location: $location);
            $this->components->info(string: "Cached content and navigation: {$location}");

            return Command::SUCCESS;
        }

        $count = $contentService->cacheAll();
        $contentNavigationService->cacheAll();
        $this->components->info(string: "Cached {$count} content file(s) with navigation");

        return Command::SUCCESS;
    }
}
