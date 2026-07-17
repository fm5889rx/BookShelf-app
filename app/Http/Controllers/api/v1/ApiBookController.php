<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApiSearchBookRequest;
use App\Http\Requests\ApiStoreBookRequest;
use App\Http\Requests\ApiUpdateBookRequest;
use App\Http\Resources\BookResource;
use App\Models\Book;
use illuminate\Http\Resources\Json\AnonymousResourceCollection;

// use Illuminate\Support\Facades\Auth;

class ApiBookController extends Controller
{
    /**
     * 書籍一覧を取得する
     */
    public function index(ApiSearchBookRequest $request): AnonymousResourceCollection
    {
        $books = Book::withCount('reviews')                     // レビュー数をカウント
            ->withAvg('reviews', 'rating')                      // レビューの平均評価を計算
            ->with('genres')                                    // ジャンル情報を取得
            ->paginate(10);                                     // 10件ずつページネーション

        return BookResource::collection($books);        // BookResourceを使って、書籍一覧をJSON形式で返す
    }

    /**
     * 書籍情報を新規作成
     */
    public function store(ApiStoreBookRequest $request)
    {
        //        $user = Auth::user();                                   // ログインユーザー情報を取得

        $validated = $request->validated();                     // バリデーション済みのデータを取得

        $book = Book::create($validated);                       // 書籍を新規作成

        $book->genres()->sync($validated['genres'] ?? []);      // ジャンルIDの紐付けをピボットテーブルに保存

        return new BookResource($book);             // BookResourceを使って、作成した書籍をJSON形式で返す
    }

    /**
     * 指定の書籍の詳細情報を取得
     */
    public function show(string $id)
    {
        $book = Book::with('genres', 'reviews.user')            // ジャンル情報とレビュー情報を取得
            ->withCount('reviews')                              // レビュー数をカウント
            ->withAvg('reviews', 'rating')                      // レビューの平均評価を計算
            ->findOrFail($id);                       // 指定されたIDの書籍を取得、存在しない場合は404エラー

        return new BookResource($book);                         // BookResourceを使って、書籍詳細をJSON形式で返す
    }

    /**
     * 指定の書籍情報を更新
     */
    public function update(ApiUpdateBookRequest $request, string $id)
    {
        $validated = $request->validated();                     // バリデーション済みのデータを取得

        $book = Book::findOrFail($id);                      // 指定されたIDの書籍を取得、存在しない場合は404エラー

        $book->update($validated);                              // 書籍情報を更新

        return new BookResource($book);                     // BookResourceを使って、更新した書籍をJSON形式で返す
    }

    /**
     * 指定の書籍情報を削除
     */
    public function destroy(string $id)
    {
        $book = Book::findOrFail($id);                      // 指定されたIDの書籍を取得、存在しない場合は404エラー

        $book->delete();                                        // 書籍を削除

        return response()->json(['message' => '書籍情報の削除に成功しました']);  // 削除成功メッセージをJSON形式で返す
    }
}
