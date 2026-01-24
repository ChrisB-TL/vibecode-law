<?php

use App\Http\Controllers\User\PublicProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/profiles/{user}', PublicProfileController::class)->name('user.show');
