<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\CashbackController;
use App\Http\Controllers\Api\V1\RewardStatusController;
use App\Http\Controllers\Api\V1\MerchantController;
use App\Http\Controllers\AuthController;

Route::prefix('v1')
    ->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
    });


Route::prefix('v1')
    ->middleware('auth:sanctum')
    ->group(function () {
        Route::get('/user', [AuthController::class, 'user']);
        Route::post('/logout', [AuthController::class, 'logout']);

        Route::post('/play', CashbackController::class);

        Route::get('/reward_status', RewardStatusController::class);

        Route::get('/merchants', MerchantController::class);
    });