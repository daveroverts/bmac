<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\LocalLoginController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Local Routes
|--------------------------------------------------------------------------
|
| Loaded from bootstrap/app.php only when APP_ENV is local.
|
*/

Route::post('/local-login/{role}', LocalLoginController::class)
    ->whereIn('role', ['admin', 'user'])
    ->name('login.local');
