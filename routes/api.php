<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Rotas de comunicação com o Master
Route::prefix('master')->middleware('master.token')->group(function () {
    Route::get('/health', [\App\Http\Controllers\Api\MasterController::class, 'health']);
    Route::get('/stats', [\App\Http\Controllers\Api\MasterController::class, 'stats']);
    Route::post('/suspend', [\App\Http\Controllers\Api\MasterController::class, 'suspend']);
    Route::post('/reactivate', [\App\Http\Controllers\Api\MasterController::class, 'reactivate']);
    Route::post('/config', [\App\Http\Controllers\Api\MasterController::class, 'config']);
});
