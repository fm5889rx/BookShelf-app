<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Session\Store;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


class BookController extends Controller
{
    /**
     * 書籍一覧画面を表示
     */
    public function index()
    {
        $books = Book::orderBy('id', 'asc')->paginate(10);      // 登録されている書籍情報を10件・ページで取得する

        return view('books.index', compact('books'));           // 書籍一覧画面を表示
    }

    /**
     * 書籍登録画面を表示
     */
    public function create()
    {
        if (!Auth::check()) {                                   // ログイン済みかチェック
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

        if (!$userId) {                                         // ログインユーザがあるかチェック
            return redirect()->route('login');                  // 未ログインならばログイン画面へリダイレクト
        }

        $book = Book::create([                              // バリデーション済みデータとユーザーIDをテーブルに保存
            'title' => $validated['title'],
            'author' => $validated['author'],
            'isbn' => $validated['isbn'],
            'published_date' => $validated['published_date'],
            'description' => $validated['description'],
            'image_url' => $validated['image_url'],
            'user_id' => $userId,
        ]);

        $book->genres()->sync($validated['genres'] ?? []);      // ジャンルIDの紐付けをピボットテーブルに保存

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
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
//        if (!Auth::check()) {                                   // ログイン済みかチェック
//            return redirect()->route('login');                  // 未ログインならばログイン画面へリダイレクト
//        }
//
//        $book = Book::findOrFail($id);                          // 指定IDの書籍情報を取得
        $this->authorize('update', Book::findOrFail($id));      // ログインユーザーが書籍情報の作成者かpolicyでチェック

        $genres = Genre::all();                                 // 登録ジャンルを全て取得

        return view('books.edit', compact('book', 'genres'));   // 書籍情報編集画面を表示
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBookRequest $request, string $id)
    {
//        if (!Auth::check()) {                                   // ログイン済みかチェック
//            return redirect()->route('login');                  // 未ログインならばログイン画面へリダイレクト
//        }
//
//        $book = Book::findOrFail($id);                          // 指定IDの書籍情報を取得

        $this->authorize('update', Book::findOrFail($id));      // ログインユーザーが書籍情報の作成者かpolicyでチェック
        $validated = $request->validated();                     // 入力された書籍データのバリデーションチェック

        $book->update($validated);                              // バリデーション済みのデータでレコードを更新

        $book->genres()->sync($validated['genres'] ?? []);      // ジャンルIDの紐付けをピボットテーブルに保存

        return redirect()->route('books.index');                // 書籍一覧にリダイレクト
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
//        if (!Auth::check()) {                                   // ログイン済みかチェック
//            return redirect()->route('login');                  // 未ログインならばログイン画面へリダイレクト
//        }
//
//        $book = Book::findOrFail($id);                          // 指定IDの書籍情報を取得

        $this->authorize('delete', Book::findOrFail($id));      // ログインユーザーが書籍情報の作成者かpolicyでチェック

        $book->delete();

        return redirect()->route('books.index');
    }
}
