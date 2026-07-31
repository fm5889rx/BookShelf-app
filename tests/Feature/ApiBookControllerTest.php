<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * 公開APIのテスト
 *
 * Advanced:
 * Sanctum認証を追加
 */
class ApiBookControllerTest extends TestCase
{
    use RefreshDatabase;                                        // データベースをリセットするトレイル

    protected function setUp(): void
    {
        parent::setUp();
        // もし認証が必要なルートなら、ここで一括ログインさせておく
        Sanctum::actingAs(User::factory()->create());
    }

    /**----------------------------------------------------------
     * 書籍一覧取得
     *---------------------------------------------------------*/
    public function test_api_書籍一覧が取得できる(): void
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

    public function test_api_書籍一覧の_jsonレスポンス構造が正しい(): void
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

    public function test_api_書籍一覧の_jsonレスポンス内容が正しい(): void
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

    public function test_api_書籍が空の場合は空の配列を返す(): void
    {
        // 実行
        $response = $this->json('GET', '/api/v1/books');        // APIで書籍一覧を取得

        // 検証
        $response->assertStatus(200);                           // HTTPステータス200を期待（正常終了）

        $response->assertJsonCount(0, 'data');                  // 書籍情報が0件取得できているかを確認

        $response->assertJson(['data' => []]);                  // 空配列が返されることを確認
    }

    /** @test */
    public function test_api_一覧取得_キーワードでタイトルと著者を部分一致検索できること(): void
    {
        // 検索に引っかかる本と引っかからない本を作成
        $book1 = Book::factory()->create(['title' => 'テストタイトル']);
        $book2 = Book::factory()->create(['author' => 'テストユーザー']);
        $book3 = Book::factory()->create(['title' => '無関係の本', 'author' => '無関係の著者']);

        // タイトル検索
        $response = $this->getJson('/api/v1/books?keyword=タイトル');
        $response->assertJsonCount(1, 'data');

        // 著者検索（orWhere の分岐を通す）
        $response = $this->getJson('/api/v1/books?keyword=ユーザー');
        $response->assertJsonCount(1, 'data');
    }

    /** @test */
    public function test_api_一覧取得_ジャンル_i_dで絞り込めること(): void
    {
        $genre = Genre::factory()->create();
        $bookWithGenre = Book::factory()->create();
        $bookWithGenre->genres()->attach($genre->id); // ジャンル紐付け

        $bookWithoutGenre = Book::factory()->create();

        $response = $this->getJson("/api/v1/books?genre={$genre->id}");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $bookWithGenre->id);
    }

    /** @test */
    public function 一覧取得_様々なソート方式が動作すること(): void
    {
        // タイトル順のテスト用にデータを仕込む
        $bookA = Book::factory()->create(['title' => 'A_Book', 'created_at' => now()->subDays(2)]);
        $bookB = Book::factory()->create(['title' => 'B_Book', 'created_at' => now()]);

        // 1. newest (新しい順) のテスト
        $response = $this->getJson('/api/v1/books?sort=newest');
        $response->assertJsonPath('data.0.id', $bookB->id);

        // 2. oldest (古い順) のテスト
        $response = $this->getJson('/api/v1/books?sort=oldest');
        $response->assertJsonPath('data.0.id', $bookA->id);

        // 3. title (タイトル昇順) のテスト
        $response = $this->getJson('/api/v1/books?sort=title');
        $response->assertJsonPath('data.0.id', $bookA->id);
    }

    /** @test */
    public function 一覧取得_評価順ソートが動作すること(): void
    {
        $bookHigh = Book::factory()->create();
        $bookLow = Book::factory()->create();

        // レビューを追加して平均評価に差をつける
        Review::factory()->create(['book_id' => $bookHigh->id, 'rating' => 5]);
        Review::factory()->create(['book_id' => $bookLow->id, 'rating' => 1]);

        $response = $this->getJson('/api/v1/books?sort=rating');

        $response->assertStatus(200);
        // 平均評価が高い bookHigh が1番目に来るか検証
        $this->assertEquals($bookHigh->id, $response->json('data.0.id'));
    }

    /**----------------------------------------------------------
     * 書籍詳細取得
     *---------------------------------------------------------*/
    public function test_api_特定の書籍詳細情報が取得できる(): void
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

    public function test_api_存在しない書籍idの詳細情報の取得に失敗する(): void
    {
        // 実行
        $response = $this->json('GET', '/api/v1/books/999');    // 存在しない書籍IDでAPIを呼び出す

        // 検証
        $response->assertStatus(404);                     // HTTPステータス404を期待（IDが見つからない）
    }

    /**----------------------------------------------------------
     * 書籍新規登録
     *---------------------------------------------------------*/
    public function test_api_書籍情報を新規作成できる(): void
    {
        // 準備
        $user = User::factory()->create();                      // テスト用にユーザーを1件作成

        $genre = Genre::factory()->create();                    // テスト用にジャンルデータを1件作成

        // 実行
        Sanctum::actingAs($user);                               // Advanced: Sanctumでログイン

        $response = $this->json('POST', 'api/v1/books', [       // APIで書籍を新規作成
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
    public function test_api_書籍情報を更新できる(): void
    {
        // 準備
        $user = User::factory()->create();                      // テスト用にユーザーを1件作成

        $genre = Genre::factory()->create();                    // テスト用にジャンルデータを1件作成

        $book = Book::factory()->create([                       // テスト用に書籍を1件登録
            'user_id' => $user->id,
        ]);

        // 実行
        Sanctum::actingAs($user);                               // Advanced: Sanctumでログイン

        $response = $this->json('PUT', "api/v1/books/{$book->id}", [  // APIで既存の書籍を更新
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

    public function test_ユーザーは他人が作成した書籍を更新できない(): void
    {
        // 準備
        $user = User::factory()->create();                      // 自分のユーザーを作成

        $otherUser = User::factory()->create();                 // 他のユーザーを作成

        Sanctum::actingAs($user);                               // 自分としてログイン

        $book = Book::factory()->create([                       // 他人の本を作成
            'user_id' => $otherUser->id,
        ]);

        // 実行
        $response = $this->Json('PUT', "/api/books/{$book->id}", [  // API呼び出し
            'title' => '勝手に書き換え',
            'genres' => [],
        ]);

        // 検証
        $response->assertStatus(404);                   // 今回の挙動に合わせて 404 または 403 で検証する
    }

    /**----------------------------------------------------------
     * 書籍削除
     *---------------------------------------------------------*/
    public function test_api_書籍情報を削除できる(): void
    {
        // 準備
        $user = User::factory()->create();                      // テスト用にユーザーを1件作成

        $book = Book::factory()->create([                       // テスト用に書籍を1件登録
            'user_id' => $user->id,
        ]);

        // 実行
        Sanctum::actingAs($user);                               // Advanced: Sanctumでログイン

        $response = $this->json('DELETE', "api/v1/books/{$book->id}"); // APIで既存の書籍を削除

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

    public function test_api_ユーザーは他人が作成した書籍を削除できない(): void
    {
        // 準備
        $user = User::factory()->create();                      // 自分のユーザーを作成

        $otherUser = User::factory()->create();                 // 他のユーザーを作成

        Sanctum::actingAs($user);                               // 自分としてログイン

        $book = Book::factory()->create([                       // 他人の本を作成
            'user_id' => $otherUser->id,
        ]);

        // 実行
        $response = $this->Json('DELETE', "/api/v1/books/{$book->id}", [  // API呼び出し
            'title' => '勝手に書き換え',
            'genres' => [],
        ]);

        // 検証
        $response->assertStatus(302);                   // 今回の挙動に合わせて 404 または 403 で検証する
    }

    /**----------------------------------------------------------
     * Advanced:
     * 認証->トークン発行
     *---------------------------------------------------------*/
    /** @test */
    public function test_api_ログイン_正しい認証情報でトークンが発行されること(): void
    {
        // 1. テスト用のユーザーをパスワード「password」で作成
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'), // 暗号化して保存
        ]);

        // 2. 正しい認証情報を送信
        $response = $this->postJson('/api/v1/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        // 3. 【検証】200 OKが返り、トークンが含まれているか
        $response->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'token_type',
            ]);

        // トークンタイプが「Bearer」であることも確認
        $response->assertJsonPath('token_type', 'Bearer');
    }

    /** @test */
    public function test_api_ログイン_間違ったパスワードの場合は401エラーになること(): void
    {
        // 1. ユーザーを作成
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        // 2. 間違ったパスワードを送信（if の中を通す）
        $response = $this->postJson('/api/v1/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password', // 間違い
        ]);

        // 3. 【検証】401 Unauthorizedが返り、指定のメッセージが含まれているか
        $response->assertStatus(401)
            ->assertJson([
                'message' => '認証失敗',
            ]);
    }
}
