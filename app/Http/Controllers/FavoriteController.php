<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    /**
     * お気に入り一覧ページを表示するメソッド
     */
    public function index()
    {
        $books = auth()->user()                                         // お気に入りがついている書籍情報を取得
                    ->favoriteBooks()
                    ->orderBy('id', 'asc')
                    ->paginate(10);

        return view('favorites.index', compact('books'));               // お気に入り一覧ページを表示
    }

    /**
     * お気に入りを追加／削除するメソッド
     */
    public function toggle(Request $request)
    {
        $book = $request->book;                                         // リクエストから書籍情報を取得

        $user = auth()->user();                                         // ログインユーザー情報を取得

        $user->favoriteBooks()->toggle($book);                          // お気に入りの追加／削除を実行

        return redirect()->back();                                      // 前のページにリダイレクト
    }

    /**
     * お気に入りランキングページを表示するメソッド
     */
    public function ranking()
    {
        $rankedBooks = Book::withCount('reviews')                       // レビュー数をカウント
                        ->withAvg('reviews', 'rating')                  // レビューの平均評価を計算
                        ->orderBy('reviews_avg_rating', 'desc')         // 平均レビュー評価点の降順で並び替え
                        ->take(10)                                      // 上位10件を取得
                        ->get();

        return view('ranking.index', compact('rankedBooks'));         // 平均レビュー評価点ランキングページを表示
    }
}
