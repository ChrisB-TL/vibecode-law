<?php

namespace App\Providers;

use App\Models\Showcase\Showcase;
use App\Models\User;
use App\Policies\Showcase\ShowcasePolicy;
use App\Policies\UserPolicy;
use App\Services\Content\ContentNavigationService;
use App\Services\Content\ContentService;
use App\Services\Markdown\MarkdownService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(
            abstract: MarkdownService::class,
            concrete: MarkdownService::class
        );

        $this->app->singleton(
            abstract: ContentService::class,
            concrete: ContentService::class
        );

        $this->app->singleton(
            abstract: ContentNavigationService::class,
            concrete: ContentNavigationService::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::shouldBeStrict();

        Gate::before(function (User $user, string $ability) {
            if ($user->is_admin) {
                return true;
            }
        });

        Gate::define('access-staff', function (User $user) {
            return $user->hasRole('Moderator');
        });

        Gate::policy(Showcase::class, ShowcasePolicy::class);
        Gate::policy(User::class, UserPolicy::class);
    }
}
