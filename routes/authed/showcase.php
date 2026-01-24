<?php

use App\Http\Controllers\Showcase\ManageShowcase\ShowcaseCreateController;
use App\Http\Controllers\Showcase\ManageShowcase\ShowcaseDestroyController;
use App\Http\Controllers\Showcase\ManageShowcase\ShowcaseDismissCelebrationController;
use App\Http\Controllers\Showcase\ManageShowcase\ShowcaseEditController;
use App\Http\Controllers\Showcase\ManageShowcase\ShowcaseStoreController;
use App\Http\Controllers\Showcase\ManageShowcase\ShowcaseUpdateController;
use App\Http\Controllers\Showcase\ManageShowcaseDraft\ShowcaseDraftDestroyController;
use App\Http\Controllers\Showcase\ManageShowcaseDraft\ShowcaseDraftEditController;
use App\Http\Controllers\Showcase\ManageShowcaseDraft\ShowcaseDraftStoreController;
use App\Http\Controllers\Showcase\ManageShowcaseDraft\ShowcaseDraftSubmitController;
use App\Http\Controllers\Showcase\ManageShowcaseDraft\ShowcaseDraftUpdateController;
use App\Http\Controllers\Showcase\ShowcaseUpvoteController;
use Illuminate\Support\Facades\Route;

// Authenticated showcase routes
Route::prefix('showcase')->name('showcase.manage.')->group(function () {
    Route::get('/create', ShowcaseCreateController::class)->name('create');
    Route::post('/', ShowcaseStoreController::class)->name('store');
    Route::get('/{showcase}/edit', ShowcaseEditController::class)->name('edit');
    Route::put('/{showcase}', ShowcaseUpdateController::class)->name('update');
    Route::delete('/{showcase}', ShowcaseDestroyController::class)->name('destroy');
    Route::post('/{showcase}/dismiss-celebration', ShowcaseDismissCelebrationController::class)->name('dismiss-celebration');

    // Upvotes
    Route::post('/{showcase}/toggle-upvote', ShowcaseUpvoteController::class)->name('toggle-upvote');
});

// Showcase draft routes
Route::prefix('showcase/draft')->name('showcase.draft.')->group(function () {
    Route::post('/{showcase}', ShowcaseDraftStoreController::class)->name('store');
    Route::get('/{draft}/edit', ShowcaseDraftEditController::class)->name('edit');
    Route::put('/{draft}', ShowcaseDraftUpdateController::class)->name('update');
    Route::delete('/{draft}', ShowcaseDraftDestroyController::class)->name('destroy');
    Route::post('/{draft}/submit', ShowcaseDraftSubmitController::class)->name('submit');
});
