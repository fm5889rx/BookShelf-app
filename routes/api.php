<?php

/**
 * Advanced:
 * Sanctum対応
 */
use App\Http\Controllers\api\v1\ApiBookController;
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
use Illuminate\Support\Facades\Route;

Route::post('/v1/login', [ApiBookController::class, 'login']);                  // Advanced:
Route::get('/v1/books', [ApiBookController::class, 'index']);
Route::get('/v1/books/{book}', [ApiBookController::class, 'show']);
Route::middleware('auth:sanctum')->group(function () {                          // Advanced:
    Route::post('/v1/books', [ApiBookController::class, 'store']);
    Route::put('/v1/books/{book}', [ApiBookController::class, 'update']);
    Route::delete('/v1/books/{book}', [ApiBookController::class, 'destroy']);
});
