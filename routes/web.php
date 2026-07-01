<?php

use Doctrine\DBAL\Schema\Index;
use Illuminate\Support\Facades\Route;

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
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\ReviewController;
use Symfony\Component\Routing\Annotation\Route as AnnotationRoute;

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
});

// レビューのルーティング
Route::middleware('auth')->group(function () {
    Route::get('/reviews/{review}/edit', [ReviewController::class, 'edit'])->name('reviews.edit');
    Route::post('/reviews/{review}', [ReviewController::class, 'like'])->name('reviews.like');
    Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    Route::put('/reviews/{review}', [ReviewController::class, 'update'])->name('reviews.update');
    Route::delete('reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
});

//仮ルート
Route::middleware('auth')->group(function () {
    Route::get('/genres', fn() => 'ジャンル一覧（準備中）')->name('genres.index');
    Route::get('/favorites', fn() => 'お気に入り一覧（準備中）')->name('favorites.index');
    Route::post('/books/{book}/favorites', fn() => 'お気に入りトグル（準備中）')->name('favorites.toggle');
    Route::get('/ranking', fn() => 'ランキング一覧（準備中）')->name('ranking.index');
});
