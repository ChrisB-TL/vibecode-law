<?php

use App\Http\Controllers\User\EditProfileController;
use App\Http\Controllers\User\PasswordController;
use App\Http\Controllers\User\UserShowcaseIndexController;
// use App\Http\Controllers\User\TwoFactorAuthenticationController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth'])->group(function () {
    Route::redirect('user-area', '/user-area/profile');

    Route::get('user-area/profile', [EditProfileController::class, 'edit'])->name('user-area.profile.edit');
    Route::patch('user-area/profile', [EditProfileController::class, 'update'])->name('user-area.profile.update');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::delete('user-area/profile', [EditProfileController::class, 'destroy'])->name('user-area.profile.destroy');

    Route::get('user-area/showcases', UserShowcaseIndexController::class)->name('user-area.showcases.index');

    Route::get('user-area/password', [PasswordController::class, 'edit'])->name('user-area.password.edit');

    Route::put('user-area/password', [PasswordController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('user-area.password.update');

    Route::get('user-area/appearance', function () {
        return Inertia::render('user-area/appearance');
    })->name('user-area.appearance.edit');

    // Route::get('user-area/two-factor', [TwoFactorAuthenticationController::class, 'show'])
    //     ->name('user-area.two-factor.show');
});
