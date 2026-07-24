<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Models\Book;
use App\Models\Genre;
use Illuminate\Http\Request;                                    // Advanced;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;                            // Advanced:

/**
 * 書籍関連のコントローラ
 * 
 * Advanced:
 * ・書籍一覧画面に検索機能追加
 * ・ISBN検索機能を追加
 */
class BookController extends Controller
{
    public function index(Request $request)
    {
        /**
         * Advanced:
         * 検索処理追加
         */
        $keyword = $request->query('keyword');                  // クエリパラメータからキーワードを分離

        $genre = intval($request->query('genre'));              // クエリパラメータからジャンルIDを分離

        $sortMethod = $request->query('sort');                  // クエリパラメータからソート方式を分離

        $query = Book::query();                                 // bookモデルのクエリビルダを取得

        if (!empty($keyword)) {                                 // キーワード指定あり？

            $query->where('title', 'like' , '%'.$keyword.'%')   // クエリビルダに書籍タイトルの部分一致検索条件を追加
                ->orWhere('author', 'like', '%'.$keyword.'%');  // クエリビルダに著者の部分一致検索条件を追加
        }

        if (!empty($genre)) {                                   // ジャンル指定あり？

            $query->whereHas('genres', function ($q) use ($genre) { // ピボットテーブルから指定ジャンルに
                $q->where('genres.id', $genre);                 // 一致する書籍をクエリビルダに追加

            });
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

            default:                                            // 上記以外（バグ回避）
                break;                                          // 何もしない
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
    public function store(StoreBookRequest $request)
    {
        $validated = $request->validated();                     // 入力データのバリデーション結果を保存

        $userId = auth()->id();                                 // ログインユーザーIDを取得

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
    public function show(string $id)
    {
        $book = Book::findOrFail($id);                          // 指定IDの書籍情報を取得

        return view('books.show', compact('book'));             // 書籍詳細画面を表示
    }

    /**
     * 書籍編集画面を表示
     */
    public function edit(string $id)
    {
        $book = Book::findOrFail($id);                          // 指定IDの書籍情報を取得

        $this->authorize('edit', $book);            // ログインユーザーが書籍情報の作成者かpolicyでチェック

        $genres = Genre::all();                                 // 登録ジャンルを全て取得

        return view('books.edit', compact('book', 'genres'));   // 書籍情報編集画面を表示
    }

    /**
     * 書籍情報更新
     */
    public function update(UpdateBookRequest $request, string $id)
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
    public function destroy(string $id)
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
    public function searchByIsbn(Request $request)
    {
        $isbn = $request->query('isbn');                        // クエリパラメータからISBNコードを取得

        // Google Books API へのリクエスト
        $response = Http::get('https://www.googleapis.com/books/v1/volumes', [
            'q' => 'isbn:' . $isbn,   // ISBN で検索
        ]);
dd($response);
        if (! $response->ok()) {                            // HTTP ステータス 200 でなければエラーにする

            return response()->json([                       // エラーメッセージを返す

                'error' => 'Google Books API への問い合わせに失敗しました',

                'code'  => $response->status(),

            ], 502);                                        // 502 Bad Gateway を返す
        }
dd($response->json());
        return response()->json($response->json());
    }
}
