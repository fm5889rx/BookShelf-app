<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchBookRequest;
use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Models\Book;
use App\Models\Genre;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;                            // Advanced:
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

/**
 * 書籍関連のコントローラ
 *
 * Advanced:
 * ・書籍一覧画面に検索機能追加
 * ・ISBN検索機能を追加
 */
class BookController extends Controller
{
    /**
     * 書籍一覧画面を表示
     *
     * Advanced:
     * 検索処理追加
     */
    public function index(SearchBookRequest $request): View
    {
        // クエリパラメータまたはセッションからキーワードを取得
        if ($request->has('keyword')) {                         // クエリパラメータに'keyword'がある？

            $keyword = $request->query('keyword');              // クエリパラメータからキーワードを取得

            Session::put('keyword', $keyword);                  // 取得したキーワードをセッションに保存

        } else {                                                // 'keyword'キーがない場合

            $keyword = Session::get('keyword');                 // セッションからキーワードを取り出す

        }

        // クエリパラメータまたはセッションからジャンルIDを取得
        if ($request->has('genre')) {                           // クエリパラメータに'genre'がある？

            $genreId = $request->query('genre');                // クエリパラメータからジャンルIDを取得

            Session::put('genre', $genreId);                    // 取得したジャンルIDをセッションに保存

        } else {                                                // 'genre'キーがない場合

            $genreId = Session::get('genre');                   // セッションからジャンルIDを取り出す

        }

        // クエリパラメータまたはセッションからソート方式を取得
        if ($request->has('sort')) {                            // クエリパラメータに'sort'がある？

            $sortMethod = $request->query('sort');              // クエリパラメータからソート方式を取得

            Session::put('sort', $sortMethod);                  // 取得したソート方式をセッションに保存

        } else {                                                // 'sort'キーがない場合

            $sortMethod = Session::get('sort');                 // セッションからソート方式を取り出す

        }

        $query = Book::query();                                 // bookモデルのクエリビルダを取得

        if (! empty($keyword)) {                                 // キーワード指定あり？

            $query->where('title', 'like', '%'.$keyword.'%')   // クエリビルダに書籍タイトルの部分一致検索条件を追加
                ->orWhere('author', 'like', '%'.$keyword.'%');  // クエリビルダに著者の部分一致検索条件を追加
        }

        if (! empty($genreId)) {                                 // ジャンル指定あり？

            $query->whereHas('genres', fn ($q) =>               // ピボットテーブルから指定ジャンルに
                $q->where('genres.id', $genreId));               // 一致する書籍をクエリビルダに追加

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

        $books = $query->paginate(10);                          // 検索結果を10件／ページで取得する

        $genres = Genre::all();                                 // Advanced:登録ジャンル全てを取得

        return view('books.index', compact('books', 'genres')); // 書籍一覧画面を表示/Advanced:ジャンル追加
    }

    /**
     * 書籍登録画面を表示
     */
    public function create()
    {
        if (! Auth::check()) {                                  // ログイン済みかチェック
            return redirect()->route('login');                  // 未ログインならばログイン画面へリダイレクト
        }

        $genres = Genre::all();                                 // 登録ジャンルを全て取得

        return view('books.create', compact('genres'));         // 書籍登録画面を表示
    }

    /**
     * 書籍登録処理
     */
    public function store(StoreBookRequest $request): RedirectResponse
    {
        $validated = $request->validated();                     // 入力データのバリデーション結果を保存

        $userId = auth()->id();                                 // ログインユーザーIDを取得

        $this->authorize('create', Book::class);          // ログインユーザーが存在するかpolicyでチェック

        if (! $userId) {                                        // ログインユーザがあるかチェック
            return redirect()->route('login');              // 未ログインならばログイン画面へリダイレクト
        }

        $book = Book::create([                      // バリデーション済みデータとユーザーIDをテーブルに保存
            'title' => $validated['title'],
            'author' => $validated['author'],
            'isbn' => $validated['isbn'],
            'published_date' => $validated['published_date'],
            'description' => $validated['description'],
            'image_url' => $validated['image_url'],
            'user_id' => $userId,
        ]);

        $book->genres()->sync($validated['genres'] ?? []);   // ジャンルIDの紐付けをピボットテーブルに保存

        return redirect()->route('books.index');                // 書籍一覧画面にリダイレクト
    }

    /**
     * 書籍詳細画面を表示
     */
    public function show(string $id): View
    {
        $book = Book::findOrFail($id);                          // 指定IDの書籍情報を取得

        return view('books.show', compact('book'));             // 書籍詳細画面を表示
    }

    /**
     * 書籍編集画面を表示
     */
    public function edit(string $id): View
    {
        $book = Book::findOrFail($id);                          // 指定IDの書籍情報を取得

        $genres = Genre::all();                                 // 登録ジャンルを全て取得

        return view('books.edit', compact('book', 'genres'));   // 書籍情報編集画面を表示
    }

    /**
     * 書籍情報更新
     */
    public function update(UpdateBookRequest $request, string $id): RedirectResponse
    {
        $book = Book::findOrFail($id);                          // 指定IDの書籍情報を取得

        $this->authorize('update', $book);          // ログインユーザーが書籍情報の作成者かpolicyでチェック

        $validated = $request->validated();                 // 入力された書籍データのバリデーションチェック

        $book->update($validated);                          // バリデーション済みのデータでレコードを更新

        $book->genres()->sync($validated['genres'] ?? []);   // ジャンルIDの紐付けをピボットテーブルに保存

        return redirect()->route('books.index');                // 書籍一覧にリダイレクト
    }

    /**
     * 書籍情報削除
     */
    public function destroy(string $id): RedirectResponse
    {
        $book = Book::findOrFail($id);                          // 指定IDの書籍情報を取得

        $this->authorize('delete', $book);          // ログインユーザーが書籍情報の作成者かpolicyでチェック

        $book->delete();                                        // レコードを削除

        return redirect()->route('books.index');                // 書籍一覧画面へリダイレクト
    }

    /**
     * Advanced:
     * Google Book APIを使って書籍情報を取得
     */
    public function searchByIsbn(string $isbn): JsonResponse
    {
        $apiKey = config('services.google.books_api_key');  // Google Books API のキーを.envから取得

        $url = 'https://www.googleapis.com/books/v1/volumes'; // APIのエンドポイントURLをセット

        $fullUrl = $url.'?q=isbn:'.$isbn.'&key='.$apiKey;

        // Google Books API へのリクエスト
        $apiResponse = Http::retry(1, 1000)->get($fullUrl); // 1秒のリトライを入れてBooks APIをコール

        if (! $apiResponse->ok()) {                         // HTTP ステータス 200 でなければエラーにする

            return response()->json([                       // JSON形式でエラー情報を返す

                'error' => 'Google Books API への問い合わせに失敗しました', // エラーメッセージ

                'code' => $apiResponse->status(),          // Google APIからのエラーコード

            ], $apiResponse->status());                     // Google APIからのエラーコードを返す
        }

        // APIレスポンスからbladeに渡せるようにレスポンスを整形
        $data = $apiResponse->json();                       // APIレスポンスをJSON形式に変換（念のため）

        if (isset($data['items'][0]['volumeInfo'])) {       // データがセットされているか？

            $volumeInfo = $data['items'][0]['volumeInfo']; // 書籍データ部分を取り出し

            // 配列形のauthors[]があるか？
            if (isset($volumeInfo['authors']) && is_array($volumeInfo['authors'])) {

                $volumeInfo['author'] = implode(', ', $volumeInfo['authors']); // 配列要素をカンマで結合

            } else {

                $volumeInfo['author'] = '';                // 著者情報がない場合は空文字をセット

            }

            $response = $volumeInfo;                        // 整形しだデータをレスポンスにセット

        } else {

            return response()->json([                       // JSON形式でエラー情報を返す

                'error' => '書籍検索に失敗しました',            // エラーメッセージ

                'code' => $apiResponse->status(),          // Google APIからのエラーコードをセット

            ], 404);                                        // 404 Not Found エラーコードを返す

        }

        return response()->json($response);                 // 整形したデータを返す
    }
}
