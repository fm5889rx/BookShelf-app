<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * 書籍情報CRUDのテスト
 */
class BookControllerTest extends TestCase
{
    use RefreshDatabase;                                        // データベースをリフレッシュするトレイト

    public function test_書籍一覧を表示できる_パターン1(): void
    {
        // 準備
        $books = Book::factory()->count(5)->create();           // テスト用に署移籍情報を5件生成

        // 実行
        $response = $this->get(route('books.index', [           // 書籍一覧を取得
            'keyword' => $books[1]->title,
            'genre_id' => 1,
            'sort' => 'newest',
            'per_page' => 10,
            'page' => 1,
        ]));

        // 検証
        $response->assertStatus(200);                           // ステータス200が返ってくることを期待

        $response->assertViewIs('books.index');                 // 書籍一覧画面が表示されていることを確認

        $response->assertViewHas('books');                      // 書籍一覧にデータが渡っていることを確認

        $books = $response->viewData('books');                  // 画面一覧ビューに渡っているデータを取得

        $this->assertNotEmpty($books);                          // データが空でないことを確認

        $this->assertEquals(10, $books->perPage());             // 10件/ページでページネーションしているか
    }

    public function test_書籍一覧を表示できる_パターン2(): void
    {
        // 準備
        $books = Book::factory()->count(5)->create();           // テスト用に署移籍情報を5件生成

        // 実行
        $response = $this->get(route('books.index', [           // 書籍一覧を取得
            'genre_id' => 1,
            'sort' => 'oldest',
            'per_page' => 10,
            'page' => 1,
        ]));

        // 検証
        $response->assertStatus(200);                           // ステータス200が返ってくることを期待

        $response->assertViewIs('books.index');                 // 書籍一覧画面が表示されていることを確認

        $response->assertViewHas('books');                      // 書籍一覧にデータが渡っていることを確認

        $books = $response->viewData('books');                  // 画面一覧ビューに渡っているデータを取得

        $this->assertNotEmpty($books);                          // データが空でないことを確認

        $this->assertEquals(10, $books->perPage());             // 10件/ページでページネーションしているか
    }

    public function test_書籍一覧を表示できる_パターン3(): void
    {
        // 準備
        $books = Book::factory()->count(5)->create();           // テスト用に署移籍情報を5件生成

        // 実行
        $response = $this->get(route('books.index', [           // 書籍一覧を取得
            'sort' => 'title',
            'per_page' => 10,
            'page' => 1,
        ]));

        // 検証
        $response->assertStatus(200);                           // ステータス200が返ってくることを期待

        $response->assertViewIs('books.index');                 // 書籍一覧画面が表示されていることを確認

        $response->assertViewHas('books');                      // 書籍一覧にデータが渡っていることを確認

        $books = $response->viewData('books');                  // 画面一覧ビューに渡っているデータを取得

        $this->assertNotEmpty($books);                          // データが空でないことを確認

        $this->assertEquals(10, $books->perPage());             // 10件/ページでページネーションしているか
    }

    public function test_書籍一覧を表示できる_パターン4(): void
    {
        // 準備
        $books = Book::factory()->count(5)->create();           // テスト用に署移籍情報を5件生成

        // 実行
        $response = $this->get(route('books.index', [           // 書籍一覧を取得
            'sort' => 'rating',
            'per_page' => 10,
            'page' => 1,
        ]));

        // 検証
        $response->assertStatus(200);                           // ステータス200が返ってくることを期待

        $response->assertViewIs('books.index');                 // 書籍一覧画面が表示されていることを確認

        $response->assertViewHas('books');                      // 書籍一覧にデータが渡っていることを確認

        $books = $response->viewData('books');                  // 画面一覧ビューに渡っているデータを取得

        $this->assertNotEmpty($books);                          // データが空でないことを確認

        $this->assertEquals(10, $books->perPage());             // 10件/ページでページネーションしているか
    }


    public function test_書籍一覧を表示できる_パターン5(): void
    {
        // 準備
        $books = Book::factory()->count(5)->create();           // テスト用に署移籍情報を5件生成

        $genre = Genre::factory()->create(['id' => 1]);         // テスト用にIDが１のジャンルデータを生成

        foreach ($books as $book) {
            $book->genres()->attach($genre->id);
        }
        // 実行
        $response = $this->get(route('books.index', [           // 書籍一覧を取得
            'genre' => 1,
            'per_page' => 10,
            'page' => 1,
        ]));

        // 検証
        $response->assertStatus(200);                           // ステータス200が返ってくることを期待

        $response->assertViewIs('books.index');                 // 書籍一覧画面が表示されていることを確認

        $response->assertViewHas('books');                      // 書籍一覧にデータが渡っていることを確認

        $books = $response->viewData('books');                  // 画面一覧ビューに渡っているデータを取得

        $this->assertNotEmpty($books);                          // データが空でないことを確認

        $this->assertEquals(10, $books->perPage());             // 10件/ページでページネーションしているか
    }

    public function test_書籍登録画面が表示できる(): void
    {
        // 準備
        $genres = Genre::factory()->count(3)->create();         // テスト用にジャンルデータを3件生成

        $user = User::factory()->create();                      // テスト用にユーザーデータを1件生成

        // 実行
        $response = $this->actingAs($user)                      // 書籍登録画面を表示
            ->get(route('books.create'));

        // 検証
        $response->assertStatus(200);                           // ステータス200を期待

        $response->assertViewIs('books.create');                // 書籍登録画面が表示されることを確認

        $response->assertViewHas('genres');                    // ビューにジャンルデータが渡っているか確認
    }

    public function test_未ログインでは書籍登録画面が表示できない(): void
    {
        // 準備
        $genres = Genre::factory()->count(3)->create();         // テスト用にジャンルデータを3件生成

        $user = User::factory()->create();                      // テスト用にユーザーデータを1件生成

        // 実行
        $response = $this->get(route('books.create'));          // ログインせずに書籍登録画面を表示

        // 検証
        $response->assertStatus(302);                           // ステータス302を期待

        $response->assertRedirect(route('login'));              // ログイン画面が表示されることを確認
    }

    public function test_ユーザーは書籍情報を新規登録できる(): void
    {
        // 準備
        $genre = Genre::factory()->create();                    // テスト用にジャンルデータを1件作成

        $user = User::factory()->create();                      // テスト用にユーザーデータを1件作成

        // 実行
        $response = $this->actingAs($user)                      // 新規登録処理を呼び出し
            ->post(route('books.store'), [
                'title' => 'テストタイトル',
                'author' => 'テストユーザー',
                'isbn' => '1234567890123',
                'published_date' => '2026-07-01',
                'description' => 'テスト説明',
                'image_url' => 'http://example.com/image.jpg',
                'user_id' => $user->id,
                'genres' => [$genre->id],
            ]);

        // 検証
        $response->assertStatus(302);                           // HTTPステータスが302を期待

        $response->assertRedirect(route('books.index'));        // 成功したら書籍一覧画面へリダイレクト

        $this->assertDatabaseHas('books', [                     // データベースに保存されているか確認
            'title' => 'テストタイトル',
            'author' => 'テストユーザー',
            'isbn' => '1234567890123',
            'published_date' => '2026-07-01',
            'description' => 'テスト説明',
            'image_url' => 'http://example.com/image.jpg',
            'user_id' => $user->id,
        ]);

        $bookId = DB::table('books')                            // 保存されたレコードのIDを取得
            ->where('title', 'テストタイトル')
            ->where('author', 'テストユーザー')
            ->where('isbn', '1234567890123')
            ->value('id');

        $this->assertDatabaseHas('book_genre', [                // 中間テーブルに保存されているか確認
            'book_id' => $bookId,
            'genre_id' => $genre->id,
        ]);
    }

    public function test_書籍詳細情報を表示できる(): void
    {
        // 準備
        $book = Book::factory()->create();                      // テスト用に書籍情報を1件作成

        // 実行
        $response = $this->get(route('books.show', $book->id));  // 書籍詳細画面を表示

        // 検証
        $response->assertStatus(200);                           // ステータス200が返ってくることを期待

        $response->assertViewIs('books.show');                  // 書籍詳細画面が表示されていることを確認

        $response->assertViewHas('book');                       // 書籍詳細にデータが渡っていることを確認

        $books = $response->viewData('book');                   // 画面詳細ビューに渡っているデータを取得

        $this->assertNotEmpty($books);                          // データが空でないことを確認
    }

    public function test_ユーザーは書籍情報編集画面が表示できる(): void
    {
        // 準備
        $user = User::factory()->create();                      // テスト用にユーザを1件作成

        $book = Book::factory()->create([                       // ユーザーが登録した書籍情報を1件作成
            'user_id' => $user->id,
        ]);

        // 実行
        $response = $this->actingAs($user)             // 登録ユーザーがログインした状態で書籍編集画面を表示
            ->get(route('books.edit', $book->id));

        // 検証
        $response->assertStatus(200);                           // HTTPステータスが200を期待

        $response->assertViewIs('books.edit');                  // 書籍編集画面が表示されていることを確認

        $response->assertViewHas('book');                       // 書籍編集にデータが渡っていることを確認

        $books = $response->viewData('book');                   // 編集画面ビューに渡っているデータを取得

        $this->assertNotEmpty($books);                          // データが空でないことを確認
    }

    public function test_未ログインユーザーは書籍情報編集画面が表示できない(): void
    {
        // 準備
        $user = User::factory()->create();                      // テスト用にユーザを1件作成

        $book = Book::factory()->create([                       // ユーザーが登録した書籍情報を1件作成
            'user_id' => $user->id,
        ]);

        // 実行
        $response = $this->get(route('books.edit', $book->id)); // 未ログインのまま書籍編集画面を表示

        // 検証
        $response->assertStatus(302);                           // HTTPステータスが302を期待

        $response->assertRedirect(route('login'));              // ログイン画面が表示されていることを確認
    }

    public function test_ユーザーは書籍情報を更新できる(): void
    {
        // 準備
        $genre = Genre::factory()->create();                    // テスト用にジャンルデータを1件作成

        $user = User::factory()->create();                      // テスト用にユーザーデータを1件作成

        $book = Book::factory()->create([                       // テスト用に書籍情報を1件作成
            'user_id' => $user->id,                             // 書籍情報の所有者
        ]);

        // 実行
        $response = $this->actingAs($user)                      // ログインして書籍情報更新処理を呼び出し
            ->put(route('books.update', $book->id), [
                'title' => 'テストタイトル',
                'author' => 'テストユーザー',
                'isbn' => '1234567890123',
                'published_date' => '2026-07-01',
                'description' => 'テスト説明',
                'image_url' => 'http://example.com/image.jpg',
                'genres' => [$genre->id],
            ]);

        // 検証
        $response->assertStatus(302);                           // HTTPステータスが302を期待

        $response->assertRedirect(route('books.index'));        // 成功したら書籍一覧画面へリダイレクト

        $this->assertDatabaseHas('books', [                     // データベースに保存されているか確認
            'title' => 'テストタイトル',
            'author' => 'テストユーザー',
            'isbn' => '1234567890123',
            'published_date' => '2026-07-01',
            'description' => 'テスト説明',
            'image_url' => 'http://example.com/image.jpg',
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseHas('book_genre', [                // 中間テーブルに保存されているか確認
            'book_id' => $book->id,
            'genre_id' => $genre->id,
        ]);
    }

    public function test_未ログインユーザーは書籍情報を更新できない(): void
    {
        // 準備
        $genre = Genre::factory()->create();                    // テスト用にジャンルデータを1件作成

        $user = User::factory()->create();                      // テスト用にユーザーデータを1件作成

        $book = Book::factory()->create([                       // テスト用に書籍情報を1件作成
            'user_id' => $user->id,                             // 書籍情報の所有者
        ]);

        // 実行
        $response = $this->put(route('books.update', $book->id), [ //ログインしないで書籍情報を更新
                'title' => 'テストタイトル',
                'author' => 'テストユーザー',
                'isbn' => '1234567890123',
                'published_date' => '2026-07-01',
                'description' => 'テスト説明',
                'image_url' => 'http://example.com/image.jpg',
                'genres' => [$genre->id],
            ]);

        // 検証
        $response->assertStatus(302);                           // HTTPステータスが302を期待

        $response->assertRedirect(route('login'));              // ログイン画面へリダイレクト
    }

    public function test_ユーザーは書籍情報が削除できる(): void
    {
        // 準備
        $user = User::factory()->create();                      // テスト用にユーザを1件作成

        $book = Book::factory()->create([                       // ユーザーが登録した書籍情報を1件作成
            'user_id' => $user->id,
        ]);

        // 実行
        $response = $this->actingAs($user)                // 登録ユーザーがログインした状態で書籍情報を削除
            ->delete(route('books.destroy', $book->id));

        // 検証
        $response->assertStatus(302);                           // HTTPステータス302を期待

        $response->assertRedirect(route('books.index'));        // 成功したら書籍一覧画面へリダイレクト

        $this->assertDatabaseMissing('books', [                 // データベースから削除されたか確認
            'id' => $book->id,
        ]);

        $this->assertDatabaseMissing('book_genre', [            // 中間テーブルから削除されたか確認
            'book_id' => $book->id,
        ]);
    }

    public function test_未ログインユーザーは書籍情報が削除できない(): void
    {
        // 準備
        $user = User::factory()->create();                      // テスト用にユーザを1件作成

        $book = Book::factory()->create([                       // ユーザーが登録した書籍情報を1件作成
            'user_id' => $user->id,
        ]);

        // 実行
        $response = $this->delete(route('books.destroy', $book->id)); // ログインせずに書籍を削除

        // 検証
        $response->assertStatus(302);                           // HTTPステータス302を期待

        $response->assertRedirect(route('login'));              // ログイン画面へリダイレクト
    }

    /**
     * Advanced:　ISBN検索
     */
    /** 正常系 ＊*/
    public function test_ISBN検索_正常系_書籍情報が正しく取得できること(): void
    {
        // 💡 1. 正常系は「ステータス 200」で、items[0]['volumeInfo'] の構造を返す
        Http::fake(['*' => Http::response([
                'items' => [
                    [
                        'volumeInfo' => [
                            'title' => 'テスト駆動開発',
                        ]
                    ]
                ]
            ], 200)
        ]);

        $response = $this->get(route('books.searchByIsbn', ['isbn' => '1234567890']));

        // 検証：フェイクデータがそのまま返ることを確認
        $response->assertStatus(200);
        $response->assertJson([
            'title' => 'テスト駆動開発',
        ]);
    }

    /** 異常系 **/
    public function test_ISBN検索_異常系_API問い合わせに失敗したときエラーになること(): void
    {
        // 💡 2. 異常系は「ステータス 500」で、エラー状態を返す
        Http::fake(['*' => Http::response([
                'error' => 'Internal Server Error'
            ], 500)
        ]);

        $response = $this->get(route('books.searchByIsbn', ['isbn' => '1234567890']));

        // 検証：コントローラーが作る 500 エラーの JSON 構造と完全一致させる
        $response->assertStatus(500);
        $response->assertJson([
            'error' => 'Google Books API への問い合わせに失敗しました',
            'code'  => 500,
        ]);
    }

    /** Book APIは成功でも書籍データが空の場合 **/
    public function test_ISBN検索_異常系_書籍データが存在しないとき404エラーになること(): void
    {
        // Google APIの通信は成功（200）するが、itemsが空（または存在しない）の状態
        Http::fake(['*' => Http::response([
                'items' => [] // 404エラーを出すために空配列にしておく
            ], 200)
        ]);

        $response = $this->get(route('books.searchByIsbn', ['isbn' => '1234567890']));

        // 検証：コントローラーが作る 404 エラーの JSON 構造と一致させる
        $response->assertStatus(404);
        $response->assertJson([
            'error' => '書籍検索に失敗しました',
            'code'  => 200,
        ]);
    }
}
