<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\UpdateReviewRequest;
use App\Models\Book;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * いいねボタンの処理
     */
    public function like(Request $request, Review $review)
    {
        if (!Auth::check())                                     // ログイン済みかチェック
        {
            return redirect()->route('login');                  // 未ログインなのでログイン画面へリダイレクト
        }

        $user = $request->user();                               // ログインユーザーを取得

        $user->likedReviews()->toggle($review->id);             // レビューのいいね状態を切り替え

        return redirect()->back();                              // 前のページにリダイレクト
    }

    /**
     * レビューの新規作成処理
     */
    public function store(StoreReviewRequest $request)
    {
        $validated = $request->validated();                     // バリデーション済みのデータを取得

        $bookId = $request->route('book');                      // ルートパラメータからbook_idを取得

        $validated['book_id'] = $bookId;                        // バリデーション済みのデータにbook_idを追加

        $validated['user_id'] = auth()->id();                   // バリデーション済みのデータにuser_idを追加

        Review::create($validated);                             // バリデーション済みのデータでレビューを作成

        return redirect()->back()                               // 作成後、前のページにリダイレクト
            ->with('success', 'レビューが作成されました。');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $review = Review::findOrFail($id);                      // 指定されたIDのレビューを取得、
                                                                // 存在しない場合は404エラーを返す

        return view('reviews.show', compact('review'));         // レビュー詳細ページにレビュー情報を渡す
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
//        if (!Auth::check())                                     // ログイン済みかチェック
//        {
//            return redirect()->route('login');                  // 未ログインなのでログイン画面へリダイレクト
//        }
//
//        $review = Review::with('book')->findOrFail($id);        // 指定されたIDのレビューと紐付いた書籍情報を取得、
//                                                                // 存在しない場合は404エラーを返す
        $this->authorize('update', Review::findOrFail($id));      // ログインユーザーがレビューの作成者かpolicyでチェック

        return view('reviews.edit', compact('review'));         // レビュー編集ページにレビュー情報を渡す
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateReviewRequest $request, string $id)
    {
//        if (!Auth::check())                                     // ログイン済みかチェック
//        {
//            return redirect()->route('login');                  // 未ログインなのでログイン画面へリダイレクト
//        }
//
//        $review = Review::findOrFail($id);                      // 指定されたIDのレビューを取得、
//                                                                // 存在しない場合は404エラーを返す
        $this->authorize('update', Review::findOrFail($id));      // ログインユーザーがレビューの作成者かpolicyでチェック

        $validated = $request->validated();                     // バリデーション済みのデータを取得

        $bookId = $review->book_id;                             // レビュー情報からbook_idを取得

        $validated['book_id'] = $bookId;                        // バリデーション済みのデータにbook_idを追加

        $review->update($validated);                            // バリデーション済みのデータでレビューを更新

        $book = Book::findOrFail($bookId);                      // 戻り先の書籍情報を取得、
                                                                // 存在しない場合は404エラーを返す（念の為）
        return view('books.show', compact('book'));             // 書籍詳細ページに書籍情報を渡す
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
//        if (!Auth::check())                                     // ログイン済みかチェック
//        {
//            return redirect()->route('login');                  // 未ログインなのでログイン画面へリダイレクト
//        }
//
//        $review = Review::findOrFail($id);                      // 指定されたIDのレビューを取得、
//                                                                // 存在しない場合は404エラーを返す
        $this->authorize('delete', Review::findOrFail($id));      // ログインユーザーがレビューの作成者かpolicyでチェック

        $review->delete();                                      // レビューを削除

        return redirect()->back()                               // 削除後、前のページにリダイレクト
            ->with('success', 'レビューが削除されました。');
    }
}
