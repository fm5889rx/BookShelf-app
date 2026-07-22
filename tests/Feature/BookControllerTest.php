<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * 書籍情報CRUDのテスト
 */
class BookControllerTest extends TestCase
{
    use RefreshDatabase;                                        // データベースをリフレッシュするトレイト

    public function test_書籍一覧を表示できる()
    {
        // 準備
        Book::factory()->count(11)->create();                   // テスト用に署移籍情報を11件生成

        // 実行
        $response = $this->get(route('books.index', [           // 書籍一覧を取得
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

    public function test_書籍登録画面が表示できる()
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

    public function test_未ログインでは書籍登録画面が表示できない()
    {
        // 準備
        $genres = Genre::factory()->count(3)->create();         // テスト用にジャンルデータを3件生成

        $user = User::factory()->create();                      // テスト用にユーザーデータを1件生成

        // 実行
        $response = $this->get(route('books.create'));          // ログインせずに書籍登録画面を表示

        // 検証
        $response->assertStatus(302);                           // ステータス302を期待

        $response->assertRedirect(route('login'));              // 書籍登録画面が表示されることを確認

        $response->assertViewHas('genres');                    // ビューにジャンルデータが渡っているか確認
    }

    public function test_ユーザーは書籍情報を新規登録できる()
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

    public function test_書籍詳細情報を表示できる()
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

    public function test_ユーザーは書籍情報編集画面が表示できる()
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

    public function test_未ログインユーザーは書籍情報編集画面が表示できない()
    {
        // 準備
        $user = User::factory()->create();                      // テスト用にユーザを1件作成

        $book = Book::factory()->create([                       // ユーザーが登録した書籍情報を1件作成
            'user_id' => $user->id,
        ]);

        // 実行
        $response = $this->get(route('books.edit', $book->id)); // 未ログインのまま書籍編集画面を表示

        // 検証
        $response->assertStatus(200);                           // HTTPステータスが200を期待

        $response->assertViewIs('books.edit');                  // 書籍編集画面が表示されていることを確認

        $response->assertViewHas('book');                       // 書籍編集にデータが渡っていることを確認

        $books = $response->viewData('book');                   // 編集画面ビューに渡っているデータを取得

        $this->assertNotEmpty($books);                          // データが空でないことを確認
    }

    public function test_ユーザーは書籍情報を更新できる()
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

    public function test_ユーザーは書籍情報が削除できる()
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
}
