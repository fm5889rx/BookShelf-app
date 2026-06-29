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

/**
 * ルーティング設定
 */
Route::get('/', function () {
    return redirect()->route('login');
});

// 仮ルート
Route::middleware('auth')->group(function () {
    Route::get('/books', fn() => '書籍一覧（準備中）')->name('books.index');
    Route::get('/genres', fn() => 'ジャンル一覧（準備中）')->name('genres.index');
    Route::get('/favorites', fn() => 'お気に入り一覧（準備中）')->name('favorites.index');
    Route::get('/ranking', fn() => 'ランキング一覧（準備中）')->name('ranking.index');
});

Route::post('/login', [AuthController::class, 'login'])->name('login')->middleware('guest');
Route::post('/logout', [AuthController::class, 'logout']);
Route::post('/register', [AuthController::class, 'register'])->name('register')->middleware('guest');
