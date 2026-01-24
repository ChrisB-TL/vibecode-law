<?php

namespace App\Console\Commands;

use App\Services\Content\ContentNavigationService;
use App\Services\Content\ContentService;
use Illuminate\Console\Command;

class ClearContentCacheCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'app:content:clear {--location= : Clear cache for a specific location only}';

    /**
     * @var string
     */
    protected $description = 'Clear content cache';

    public function handle(
        ContentService $contentService,
        ContentNavigationService $contentNavigationService
    ): int {
        $location = $this->option(key: 'location');

        if ($location !== null) {
            $contentCleared = $contentService->clearCache(location: $location);
            $navCleared = $contentNavigationService->clearCache(location: $location);

            if ($contentCleared === true || $navCleared === true) {
                $this->components->info(string: "Cleared cache for: {$location}");
            } else {
                $this->components->warn(string: "No cache found for: {$location}");
            }

            return Command::SUCCESS;
        }

        $contentCount = $contentService->clearAllCache();
        $navCount = $contentNavigationService->clearAllCache();
        $this->components->info(string: "Cleared {$contentCount} content cache(s) and {$navCount} navigation cache(s)");

        return Command::SUCCESS;
    }
}
