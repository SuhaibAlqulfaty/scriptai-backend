<?php

use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\ScriptController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Health check
Route::get('/health', [HealthController::class, 'check']);

// Script generation routes
Route::prefix('scripts')->group(function () {
    Route::post('/generate', [ScriptController::class, 'generate']);
    Route::get('/{id}', [ScriptController::class, 'show']);
    Route::post('/{id}/feedback', [ScriptController::class, 'feedback']);
    Route::post('/suggest-tone', [ScriptController::class, 'suggestTone']);
});

// Analytics
Route::get('/analytics', [ScriptController::class, 'analytics']);
