<?php

use App\Http\Controllers\api\v1\ApiBookController;
use Illuminate\Http\Request;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/v1/books', [ApiBookController::class, 'index']);
Route::get('/v1/books/{book}', [ApiBookController::class, 'show']);
Route::post('/v1/books', [ApiBookController::class, 'store']);
Route::put('/v1/books/{book}', [ApiBookController::class, 'update']);
Route::delete('/v1/books/{book}', [ApiBookController::class, 'destroy']);
