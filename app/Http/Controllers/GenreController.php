<?php

namespace App\Http\Controllers;

use App\Models\Genre;
use App\Http\Requests\StoreGenreRequest;
use App\Http\Requests\UpdateGenreRequest;
use Illuminate\Http\Request;

class GenreController extends Controller
{
    /**
     * ジャンル一覧フォームの表示
     */
    public function index()
    {
        $genres = Genre::withCount('books as books_count')      // ジャンルごとの書籍数をカウントして取得
            ->get();

        return view('genres.index', compact('genres'));         // ジャンル一覧ページにジャンルデータを渡す
    }

    /**
     * ジャンル新規作成フォームの表示
     */
    public function create()
    {
        return view('genres.create');                           // ジャンル新規作成ページを表示
    }

    /**
     * ジャンルの新規作成処理
     */
    public function store(StoreGenreRequest $request)
    {
        $validated = $request->validated();                     // バリデーション済みのデータを取得

        $genre = Genre::create($validated);             // バリデーション済みのデータを使用して新しいジャンルを作成

        return redirect()->route('genres.index')                // 作成後、ジャンル一覧ページにリダイレクト
            ->with('success', 'ジャンルが作成されました。');
    }

    /**
     * ジャンル詳細の表示
     */
    public function show(string $id)
    {
        $genre = Genre::findOrFail($id);            // 指定されたIDのジャンルを取得、存在しない場合は404エラーを返す

        $books = $genre->books()->get();                        // ジャンルに関連する書籍情報を取得

        return view('genres.show', compact('genre', 'books'));  // ジャンル詳細ページにジャンルデータと関連書籍データを渡す
    }

    /**
     * ジャンル編集フォームの表示
     */
    public function edit(string $id)
    {
        $genre = Genre::findOrFail($id);            // 指定されたIDのジャンルを取得、存在しない場合は404エラーを返す

        return view('genres.edit', compact('genre'));           // ジャンル編集ページにジャンルデータを渡す
    }

    /**
     * ジャンルの更新処理
     */
    public function update(UpdateGenreRequest $request, string $id)
    {
        $validated = $request->validated();                     // バリデーション済みのデータを取得

        $genre = Genre::findOrFail($id);                        // 指定されたIDのジャンルを取得

        $genre->update($validated);                             // バリデーション済みのデータでジャンルを更新

        return redirect()->route('genres.show', $genre->id)     // 更新後、ジャンル詳細ページにリダイレクト
                ->with('success', 'ジャンルが更新されました。');
    }

    /**
     * ジャンルの削除処理
     */
    public function destroy(string $id)
    {
        $genre = Genre::findOrFail($id);                        // 指定されたIDのジャンルを取得

        $genre->delete();                                       // ジャンルを削除

        return redirect()->route('genres.index')                // 削除後、ジャンル一覧ページにリダイレクト
            ->with('success', 'ジャンルが削除されました。');
    }
}
