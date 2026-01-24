<?php

use App\Http\Controllers\Auth\LinkedinAuthCallbackController;
use App\Http\Controllers\Auth\LinkedinAuthRedirectController;
use Illuminate\Support\Facades\Route;

Route::middleware(['guest'])->name('auth.login.linkedin.')->group(function () {
    Route::get('/auth/login/linkedin/redirect', LinkedinAuthRedirectController::class)->name('redirect');
    Route::get('/auth/login/linkedin/callback', LinkedinAuthCallbackController::class)->name('callback');
});
