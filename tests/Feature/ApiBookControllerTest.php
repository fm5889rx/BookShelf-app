<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 *  公開APIのテスト
 */
class ApiBookTest extends TestCase
{
    use RefreshDatabase;                // データベースをリセットするトレイル

    /**----------------------------------------------------------
     * 書籍一覧取得
     *---------------------------------------------------------*/
    public function test_api_書籍一覧が取得できる()
    {
        // 準備
        $user = User::factory()->create();                      // テスト用にユーザーを1件作成

        Book::factory()->count(3)->create([                     // テスト用に書籍情報を3件作成
            'user_id' => $user->id,
        ]);

        // 実行
        $response = $this->json('GET', '/api/v1/books');        // APIで書籍一覧を取得

        // 検証
        $response->assertStatus(200);                           // HTTPステータス200を期待（正常終了）

        $response->assertJsonCount(3, 'data');                  // 書籍情報が3件取得できているかを確認
    }

    public function test_api_書籍一覧の_jso_nレスポンス構造が正しい()
    {
        // 準備
        $user = User::factory()->create();                      // テスト用にユーザーを1件作成

        $genre = Genre::factory()->create([                     // テスト用にジャンルを1件作成
            'name' => 'テストジャンル',
        ]);

        Book::factory()->create([                               // テスト用に書籍情報を1件作成
            'user_id' => $user->id,
        ]);

        // 実行
        $response = $this->json('GET', '/api/v1/books');        // APIで書籍一覧を取得

        // 検証
        $response->assertStatus(200);                           // HTTPステータス200を期待（正常終了）

        $response->assertJsonStructure([                        // レスポンスが正しいJSON形式か確認
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'author',
                    'isbn',
                    'published_date',
                    'description',
                    'image_url',
                    'user_id',
                ],
            ],
        ]);
    }

    public function test_api_書籍一覧の_jso_nレスポンス内容が正しい()
    {
        // 準備
        $user = User::factory()->create();                      // テスト用にユーザーを1件作成

        $genre = Genre::factory()->create([                     // テスト用にジャンルを1件作成
            'name' => 'テストジャンル',
        ]);

        $book = Book::factory()->create([                       // テスト用に書籍情報を1件作成
            'user_id' => $user->id,
        ]);

        // 実行
        $response = $this->json('GET', '/api/v1/books');        // APIで書籍一覧を取得

        // 検証
        $response->assertStatus(200);                           // HTTPステータス200を期待（正常終了）

        $response->assertJsonFragment([                         // JSONレスポンス内容が合っているか確認
            'id' => $book->id,
            'title' => $book->title,
            'author' => $book->author,
            'isbn' => $book->isbn,
            'published_date' => $book->published_date,
            'description' => $book->description,
            'image_url' => $book->image_url,
            'user_id' => $book->user_id,
        ]);
    }

    public function test_api_書籍が空の場合は空の配列を返す()
    {
        // 実行
        $response = $this->json('GET', '/api/v1/books');        // APIで書籍一覧を取得

        // 検証
        $response->assertStatus(200);                           // HTTPステータス200を期待（正常終了）

        $response->assertJsonCount(0, 'data');                  // 書籍情報が0件取得できているかを確認

        $response->assertJson(['data' => []]);                  // 空配列が返されることを確認
    }

    /**----------------------------------------------------------
     * 書籍詳細取得
     *---------------------------------------------------------*/
    public function test_api_特定の書籍詳細情報が取得できる()
    {
        // 準備
        $user = User::factory()->create();                      // テスト用にユーザーを1件作成

        $book = Book::factory()->create([                       // テスト用に書籍情報を1件作成
            'user_id' => $user->id,
        ]);

        // 実行
        $response = $this->json('GET', "/api/v1/books/{$book->id}");  // APIで書籍詳細を取得

        // 検証
        $response->assertStatus(200);                           // HTTPステータス200を期待（正常終了）

        $response->assertJsonStructure([                        // レスポンスが正しいJSON形式か確認
            'data' => [
                'id',
                'title',
                'author',
                'isbn',
                'published_date',
                'description',
                'image_url',
                'user_id',
            ],
        ]);
    }

    public function test_api_存在しない書籍_i_dの詳細商法主翼に失敗する()
    {
        // 実行
        $response = $this->json('GET', '/api/v1/books/999');    // 存在しない書籍IDでAPIを呼び出す

        // 検証
        $response->assertStatus(404);                     // HTTPステータス404を期待（IDが見つからない）
    }

    /**----------------------------------------------------------
     * 書籍新規登録
     *---------------------------------------------------------*/
    public function test_api_書籍情報を新規作成できる()
    {
        // 準備
        $user = User::factory()->create();                      // テスト用にユーザーを1件作成

        $genre = Genre::factory()->create();                    // テスト用にジャンルデータを1件作成

        // 実行
        $response = $this->json('POST', 'api/v1/books', [       // 書籍を新規作成
            'title' => 'テストタイトル',
            'author' => 'テストユーザー',
            'isbn' => '1234567890123',
            'published_date' => '2026-07-01',
            'description' => 'テスト説明',
            'image_url' => 'http://www.example.com/test.jpg',
            'user_id' => $user->id,
            'genres' => [$genre->id],
        ]);

        // 検証
        $response->assertStatus(201);                           // HTTPステータス201を期待（作成成功）

        $this->assertDatabaseHas('books', [                   // データベースに書籍が登録されているか確認
            'title' => 'テストタイトル',
            'author' => 'テストユーザー',
            'isbn' => '1234567890123',
            'published_date' => '2026-07-01',
            'description' => 'テスト説明',
            'image_url' => 'http://www.example.com/test.jpg',
            'user_id' => $user->id,
        ]);
    }

    /**----------------------------------------------------------
     * 書籍更新
     *---------------------------------------------------------*/
    public function test_api_書籍情報を更新できる()
    {
        // 準備
        $user = User::factory()->create();                      // テスト用にユーザーを1件作成

        $genre = Genre::factory()->create();                    // テスト用にジャンルデータを1件作成

        $book = Book::factory()->create([                       // テスト用に書籍を1件登録
            'user_id' => $user->id,
        ]);

        // 実行
        $response = $this->json('PUT', "api/v1/books/{$book->id}", [  // 既存の書籍を更新
            'title' => 'テストタイトル',
            'author' => 'テストユーザー',
            'isbn' => '1234567890123',
            'published_date' => '2026-07-01',
            'description' => 'テスト説明',
            'image_url' => 'http://www.example.com/test.jpg',
            'user_id' => $user->id,
            'genres' => [$genre->id],
        ]);

        // 検証
        $response->assertStatus(200);                           // HTTPステータス200を期待（正常終了）

        $this->assertDatabaseHas('books', [                   // データベースに書籍が登録されているか確認
            'title' => 'テストタイトル',
            'author' => 'テストユーザー',
            'isbn' => '1234567890123',
            'published_date' => '2026-07-01',
            'description' => 'テスト説明',
            'image_url' => 'http://www.example.com/test.jpg',
            'user_id' => $user->id,
        ]);
    }

    /**----------------------------------------------------------
     * 書籍削除
     *---------------------------------------------------------*/
    public function test_api_書籍情報を削除できる()
    {
        // 準備
        $user = User::factory()->create();                      // テスト用にユーザーを1件作成

        $book = Book::factory()->create([                       // テスト用に書籍を1件登録
            'user_id' => $user->id,
        ]);

        // 実行
        $response = $this->json('DELETE', "api/v1/books/{$book->id}");  // 既存の書籍を削除

        // 検証
        $response->assertStatus(200);                           // HTTPステータス200を期待（正常終了）

        $this->assertDatabaseMissing('books', [               // データベースから書籍が削除されているか確認
            'title' => $book->title,
            'author' => $book->author,
            'isbn' => $book->isbn,
            'published_date' => $book->published_date,
            'description' => $book->description,
            'image_url' => $book->image_url,
            'user_id' => $user->id,
        ]);
    }
}
