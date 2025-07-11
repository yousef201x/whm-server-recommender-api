<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ServerController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;

RateLimiter::for('user-server-limit', function (Request $request) {
    return Limit::perMinute(70)->by(optional($request->user())->id ?: $request->ip());
});

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:user-server-limit');


Route::middleware(['auth:sanctum', 'throttle:user-server-limit'])->group(function () {

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/server', [ServerController::class, 'store']);
});

Route::get('/recommend', [ServerController::class, 'recommend'])->middleware('throttle:user-server-limit');
