<?php

use App\Http\Controllers\ToolController;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Route::apiResource('tools', ToolController::class);