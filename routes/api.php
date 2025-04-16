<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ToolController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::apiResource('tools', ToolController::class)->only(['index']);

Route::middleware('auth:api')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::apiResource('tools', ToolController::class)->only(['update', 'destroy', 'store', 'show']);	
});