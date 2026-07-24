<?php

use App\Http\Controllers\AuthController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
/**
 * Controllerクラスの使用宣言
 */
use App\Http\Controllers\BookController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\NotificationController;            // Advanced:
use App\Http\Controllers\ReadingPlanController;             // Advanced:
use App\Http\Controllers\ReportController;                  // Advanced:
use App\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Route;

/**
 * ルーティング設定
 */
// 認証のルーティング
Route::post('/login', [AuthController::class, 'login'])->name('login')->middleware('guest');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::post('/register', [AuthController::class, 'register'])->name('register')->middleware('guest');

// 書籍情報のルーティング
Route::get('/books', [BookController::class, 'index'])->name('books.index');
Route::get('/books/create', [BookController::class, 'create'])->name('books.create');
Route::post('/books', [BookController::class, 'store'])->name('books.store');
Route::get('/books/{book}', [BookController::class, 'show'])->name('books.show');
Route::middleware('auth')->group(function () {
    Route::get('/books/{book}/edit', [BookController::class, 'edit'])->name('books.edit');
    Route::put('/books/{book}', [BookController::class, 'update'])->name('books.update');
    Route::delete('/books/{book}', [BookController::class, 'destroy'])->name('books.destroy');
    Route::get('books/isbn/{isbn}', [BookController::class, 'searchByIsbn']); // Advanced:ISBN検索
});

// レビューのルーティング
Route::middleware('auth')->group(function () {
    Route::post('/reviews/{review}/like', [ReviewController::class, 'like'])->name('reviews.like');
    Route::get('/reviews/{review}/edit', [ReviewController::class, 'edit'])->name('reviews.edit');
    Route::post('/books/{book}/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    Route::put('/reviews/{review}', [ReviewController::class, 'update'])->name('reviews.update');
    Route::delete('reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
});

// ジャンルのルーティング
Route::middleware('auth')->group(function () {
    Route::get('/genres', [GenreController::class, 'index'])->name('genres.index');
    Route::get('/genres/create', [GenreController::class, 'create'])->name('genres.create');
    Route::post('/genres', [GenreController::class, 'store'])->name('genres.store');
    Route::get('/genres/{id}', [GenreController::class, 'show'])->name('genres.show');
    Route::get('/genres/{id}/edit', [GenreController::class, 'edit'])->name('genres.edit');
    Route::put('/genres/{id}', [GenreController::class, 'update'])->name('genres.update');
    Route::delete('/genres/{id}', [GenreController::class, 'destroy'])->name('genres.destroy');
});

// その他ルート
Route::middleware('auth')->group(function () {
    Route::post('/books/{book}/favorites', [FavoriteController::class, 'toggle'])->name('favorites.toggle');
});
Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');
Route::get('/ranking', [FavoriteController::class, 'ranking'])->name('ranking.index');

/**
 * Advanced:
 * 応用機能のルーティング
 */
// 読書計画
Route::get('reading-plans', [ReadingPlanController::class, 'index'])->name('reading-plans.index');
Route::get('/reading^plans/create', [ReadingPlanController::class, 'create'])->name('reading-plans.create');
Route::get('/reading-plans/{plan}/edit', [ReadingPlanController::class, 'edit'])->name('reading-plans.edit');
Route::post('/reading-plans', [ReadingPlanController::class, 'store'])->name('reading-plans.store');
Route::put('/reading-plans/{plan}', [ReadingPlanController::class, 'update'])->name('reading-plans.update');
Route::delete('/reading-plans/{plan}', [ReadingPlanController::class, 'destroy'])->name('reading-plans.destroy');
Route::post('/reading-plans/{plan}/complete', [ReadingPlanController::class, 'complete'])->name('reading-plans.complete');

// マイ読書レポート
Route::get('reports', [ReportController::class, 'index'])->name('reports.index');

// 通知
Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
