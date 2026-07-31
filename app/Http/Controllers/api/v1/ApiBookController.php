<?php

namespace App\Http\Controllers\api\v1;

/**
 * 公開API
 *
 * Advanced:
 * 書き込み系APIをSanctum対応に変更
 */
use App\Http\Controllers\Controller;
use App\Http\Requests\ApiSearchBookRequest;
use App\Http\Requests\ApiStoreBookRequest;
use App\Http\Requests\ApiUpdateBookRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\BookResource;
use App\Models\Book;
use Illuminate\Http\JsonResponse;                               // Advanced:
use illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class ApiBookController extends Controller
{
    /**
     * 書籍一覧を取得する
     *
     * Advanced:
     * 検索パラメータ対応
     */
    public function index(ApiSearchBookRequest $request): AnonymousResourceCollection
    {
        $keyword = $request->query('keyword');                  // クエリパラメータからキーワードを取得

        $genreId = $request->query('genre');                    // クエリパラメータからジャンルIDを取得

        $sortMethod = $request->query('sort');                  // クエリパラメータからソート方式を取得

        $query = Book::query();                                 // bookモデルのクエリビルダを取得

        if (! empty($keyword)) {                                 // キーワード指定あり？

            $query->where('title', 'like', '%'.$keyword.'%')   // クエリビルダに書籍タイトルの部分一致検索条件を追加
                ->orWhere('author', 'like', '%'.$keyword.'%');  // クエリビルダに著者の部分一致検索条件を追加
        }

        if (! empty($genreId)) {                                 // ジャンル指定あり？

            $query->whereHas('genres', fn ($q) =>                // ピボットテーブルから指定ジャンルに
                $q->where('genres.id', $genreId));              // 一致する書籍をクエリビルダに追加

        }

        switch ($sortMethod) {                                  // 並び順により選択
            case 'newest':                                      // 新しい順を選択
                $query->orderBy('created_at', 'desc');          // クエリビルダに作成日時の降順を追加

                break;                                          // この処理終わり

            case 'oldest':                                      // 古い順を選択
                $query->orderBy('created_at', 'asc');           // クエリビルダに作成日時の昇順を追加

                break;                                          // この処理終わり

            case 'rating':                                      // 評価順を選択
                $query->withAvg('reviews', 'rating')            // reviewsテーブルとのクエリビルダで
                    ->orderBy('reviews_avg_rating', 'desc');    // 平均評価値の降順を追加

                break;                                          // この処理終わり

            case 'title':                                       // タイトル順を選択
                $query->orderBy('title', 'asc');                // クエリビルダにタイトル名の昇順を追加

                break;                                          // この処理終わり

            default:                                            // 上記以外
                break;                                          // この処理終わり
        }

        $books = $query->withCount('reviews')                   // レビュー数をカウント
            ->withAvg('reviews', 'rating')                      // レビューの平均評価を計算
            ->with('genres')                                    // ジャンル情報を取得
            ->paginate(10);                                     // 10件ずつページネーション

        return BookResource::collection($books);        // BookResourceを使って、書籍一覧をJSON形式で返す
    }

    /**
     * 書籍情報を新規作成
     */
    public function store(ApiStoreBookRequest $request): BookResource
    {
        $user = Auth::user();                                   // ログインユーザー情報を取得

        $validated = $request->validated();                     // バリデーション済みのデータを取得

        $this->authorize('create', Book::class);          // ログインユーザーが存在するかpolicyでチェック

        $validated['user_id'] = $user->id;                      // ログインユーザを登録者にする

        $book = Book::create($validated);                       // 書籍を新規作成

        $book->genres()->sync($validated['genres'] ?? []);  // ジャンルIDの紐付けをピボットテーブルに保存

        return new BookResource($book);             // BookResourceを使って、作成した書籍をJSON形式で返す
    }

    /**
     * 指定の書籍の詳細情報を取得
     */
    public function show(string $id): BookResource
    {
        $book = Book::with('genres', 'reviews.user')            // ジャンル情報とレビュー情報を取得
            ->withCount('reviews')                              // レビュー数をカウント
            ->withAvg('reviews', 'rating')                      // レビューの平均評価を計算
            ->findOrFail($id);                       // 指定されたIDの書籍を取得、存在しない場合は404エラー

        return new BookResource($book);                 // BookResourceを使って、書籍詳細をJSON形式で返す
    }

    /**
     * 指定の書籍情報を更新
     */
    public function update(ApiUpdateBookRequest $request, string $id): BookResource
    {
        $validated = $request->validated();                     // バリデーション済みのデータを取得

        $book = Book::findOrFail($id);               // 指定されたIDの書籍を取得、存在しない場合は404エラー

        $this->authorize('update', $book);         // ログインユーザーが書籍情報の作成者かpolicyでチェック

        $book->update($validated);                              // 書籍情報を更新

        $book->genres()->sync($validated['genres'] ?? []);  // ジャンルIDの紐付けをピボットテーブルに保存

        return new BookResource($book);             // BookResourceを使って、更新した書籍をJSON形式で返す
    }

    /**
     * 指定の書籍情報を削除
     */
    public function destroy(string $id): JsonResponse
    {
        $book = Book::findOrFail($id);               // 指定されたIDの書籍を取得、存在しない場合は404エラー

        $this->authorize('delete', $book);         // ログインユーザーが書籍情報の作成者かpolicyでチェック

        $book->delete();                                        // 書籍を削除

        return response()->json([                               // 削除成功メッセージをJSON形式で返す
            'message' => '書籍情報の削除に成功しました',
        ]);
    }

    /**
     * Advanced:
     * sanctumトークン発行
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();                     // バリデーション済みのデータを取得

        if (! Auth::guard('web')->attempt($validated)) {         // チェック済み認証情報でログインできるか

            return response()->json([                           // できない場合は401エラーを返す
                'message' => '認証失敗',
            ], 401);

        }

        $user = Auth::user();                                   // ログインユーザー情報を取得

        $token = $user->createToken('API Token')                // トークンを作成し、クライアントに返す
            ->plainTextToken;

        return response()->json([                               // 認証情報から生成したトークンを返す
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }
}
