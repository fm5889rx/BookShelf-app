<?php

namespace Tests\Unit;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiValidationTest extends TestCase
{
    use RefreshDatabase;                                // データベースをリセットするトレイト

    /** テスト共有変数 */
    protected $user;                                    // テスト用に保持するユーザー情報

    protected $genre;                                   // テスト用に保持するジャンル情報

    protected $book;                                    // テスト用に保持する書籍情報

    protected function setUp(): void
    {
        parent::setUp();

        // テスト用のusersデータを作成
        $this->user = User::factory()->create();        // テスト用のユーザーを登録

        // テスト用のジャンルテーブルを作成
        $this->genre = Genre::factory()->create();      // テスト用のジャンルを登録

        $this->book = Book::factory()->create([         // テスト用の書籍情報を1件登録
            'user_id' => $this->user->id,               // 存在するユーザーIDを使用
        ]);
    }

    /**----------------------------------------------------------------------------------------
     * GET系テスト
     *---------------------------------------------------------------------------------------*/
    /** 正常終了 **/
    public function test_api_正常系パラメータ_検索対応()
    {
        // API に対して有効なクエリパラメータを送信
        $response = $this->getJson('/api/v1/books', [
            'keyword' => '',                            // 検索対象では無い
            'genre_id' => 1,                            // このIDがgenresテーブルに存在することを想定
            'per_page' => 10,                           // 1ページあたりの一覧数は10件
            'page' => 1,                                // 1ページ目を取得
        ]);

        $response->assertSuccessful();                  // 200 系レスポンスを期待

        $response->assertJsonStructure([                // 必要に応じて返却データ構造を確認
            'data',
            'links',
            'meta',
        ]);
    }

    /** キーワード文字列が長すぎる */
    public function test_api_異常系_キーワードが長すぎる()
    {
        // 長すぎるキーワード文字列を送信してバリデーションエラーを確認
        $response = $this->json('GET', 'api/v1/books', [
            'keyword' => str_repeat('A', 256),          // max:255 に対して256文字の文字列を渡す
        ]);

        $response
            ->assertStatus(422)                         // 422レスポンスを期待
            ->assertJsonValidationErrors(['keyword']);  // キーワードに対するバリデーションエラーかを確認
    }

    /** 存在しないジャンルID */
    public function test_api_異常系_ジャンル_i_dが存在しない()
    {
        // 存在しない genre_id を送信してバリデーションエラーを確認
        $response = $this->json('GET', '/api/v1/books', [
            'genre_id' => 99999,                        // 存在しない ID
        ]);

        $response
            ->assertStatus(422)                         // 422レスポンスを期待
            ->assertJsonValidationErrors(['genre_id']); // ジャンルIDに対するバリデーションエラーかを確認
    }

    /** per_pageが範囲外 */
    public function test_api_異常系_ページネーション_per_page範囲外_最小()
    {
        // per_page は min:1 なので、0を送信してバリデーションエラーを確認
        $response = $this->json('GET', '/api/v1/books', [
            'per_page' => 0,                            // min:1 を下回る
        ]);

        $response
            ->assertStatus(422)                         // 422レスポンスを期待
            ->assertJsonValidationErrors(['per_page']); // per_pageに対するバリデーションエラーかを確認
    }

    /** per_pageが範囲外 */
    public function test_api_異常系_ページネーション_per_page範囲外_最大()
    {
        // per_page は max:100 なので、101を送信してバリデーションエラーを確認
        $response = $this->json('GET', '/api/v1/books', [
            'per_page' => 101,                          // max:100 を上回る
        ]);

        $response
            ->assertStatus(422)                         // 422レスポンスを期待
            ->assertJsonValidationErrors(['per_page']); // per_pageに対するバリデーションエラーかを確認
    }

    /** pageが範囲外 */
    public function test_api_異常系_ページ指定が範囲外()
    {
        // page は min:1 なので、0を送信してバリデーションエラーを確認
        $response = $this->json('GET', '/api/v1/books', [
            'page' => 0,                                // min:1 を下回る
        ]);

        $response
            ->assertStatus(422)                         // 422レスポンスを期待
            ->assertJsonValidationErrors(['page']);     // pageに対するバリデーションエラーかを確認
    }

    /**----------------------------------------------------------------------------------------
     * POST系テスト
     *---------------------------------------------------------------------------------------*/
    /** 正常終了 **/
    public function test_api_正常系_postで成功を返す()
    {
        $response = $this->json('POST', '/api/v1/books', [  // 書籍登録機能を呼び出す
            'title' => 'テストタイトル',
            'author' => 'テストユーザー',
            'isbn' => '1234567890123',
            'published_date' => '2026-07-01',
            'description' => 'テスト説明',
            'image_url' => 'http://example.com/test.jpg',
            'user_id' => $this->user->id,               // 存在する user_id を使用
            'genres' => [$this->genre->id],             // 存在する genre_id を使用
        ]);

        // 検証
        $response->assertStatus(201);                       // 作成成功は 201

        $this->assertDatabaseHas('books', [                 // データベースに書き込まれているか確認
            'title' => 'テストタイトル',
            'isbn' => '1234567890123',
            'user_id' => $this->user->id,
        ]);
    }

    /** タイトルが空文字列 **/
    public function test_api_異常系_postで空のtitleのバリデーションエラーを返す()
    {
        $response = $this->json('POST', '/api/v1/books', [  // 書籍登録機能を呼び出す
            'title' => '',                              // タイトルが空文字列
            'author' => 'テストユーザー',
            'isbn' => '1234567890123',
            'published_date' => '2026-07-01',
            'description' => 'テスト説明',
            'image_url' => 'http://example.com/test.jpg',
            'user_id' => $this->user->id,               // 存在する user_id を使用
            'genres' => [$this->genre->id],             // 存在する genre_id を使用
        ]);

        $response->assertStatus(422)                        // バリデーションエラーは 422
            ->assertJsonValidationErrors(['title']);        // titleに対するバリデーションエラーかを確認
    }

    /** 長すぎるタイトル文字列 **/
    public function test_api_異常系_postで長すぎるtitleのバリデーションエラーを返す()
    {
        $response = $this->json('POST', '/api/v1/books', [  // 書籍登録機能を呼び出す
            'title' => str_repeat('A', 256),            // max:255なので256文字の文字列
            'author' => 'テストユーザー',
            'isbn' => '1234567890123',
            'published_date' => '2026-07-01',
            'description' => 'テスト説明',
            'image_url' => 'http://example.com/test.jpg',
            'user_id' => $this->user->id,               // 存在する user_id を使用
            'genres' => [$this->genre->id],             // 存在する genre_id を使用
        ]);

        $response->assertStatus(422)                        // バリデーションエラーは 422
            ->assertJsonValidationErrors(['title']);        // titleに対するバリデーションエラーかを確認
    }

    /** 著者が空文字列 **/
    public function test_api_異常系_postで空のauthorのバリデーションエラーを返す()
    {
        $response = $this->json('POST', '/api/v1/books', [  // 書籍登録機能を呼び出す
            'title' => 'テストタイトル',
            'author' => '',                             // 著者が空文字列
            'isbn' => '1234567890123',
            'published_date' => '2026-07-01',
            'description' => 'テスト説明',
            'image_url' => 'http://example.com/test.jpg',
            'user_id' => $this->user->id,               // 存在する user_id を使用
            'genres' => [$this->genre->id],             // 存在する genre_id を使用
        ]);

        $response->assertStatus(422)                        // バリデーションエラーは 422
            ->assertJsonValidationErrors(['author']);       // authorに対するバリデーションエラーかを確認
    }

    /** 長すぎる著者文字列 **/
    public function test_api_異常系_postで長すぎるautherのバリデーションエラーを返す()
    {
        $response = $this->json('POST', '/api/v1/books', [  // 書籍登録機能を呼び出す
            'title' => 'テストタイトル',
            'author' => str_repeat('A', 256),           // max:255なので256文字の文字列
            'isbn' => '1234567890123',
            'published_date' => '2026-07-01',
            'description' => 'テスト説明',
            'image_url' => 'http://example.com/test.jpg',
            'user_id' => $this->user->id,               // 存在する user_id を使用
            'genres' => [$this->genre->id],             // 存在する genre_id を使用
        ]);

        $response->assertStatus(422)                        // バリデーションエラーは 422
            ->assertJsonValidationErrors(['author']);       // authorに対するバリデーションエラーかを確認
    }

    /** ISBNコードが空文字列 **/
    public function test_api_異常系_postで空のisbnのバリデーションエラーを返す()
    {
        $response = $this->json('POST', '/api/v1/books', [  // 書籍登録機能を呼び出す
            'title' => 'テストタイトル',
            'author' => 'テストユーザー',
            'isbn' => '',                               // isbnが空文字列
            'published_date' => '2026-07-01',
            'description' => 'テスト説明',
            'image_url' => 'http://example.com/test.jpg',
            'user_id' => $this->user->id,               // 存在する user_id を使用
            'genres' => [$this->genre->id],             // 存在する genre_id を使用
        ]);

        $response->assertStatus(422)                        // バリデーションエラーは 422
            ->assertJsonValidationErrors(['isbn']);         // isbnに対するバリデーションエラーかを確認
    }

    /** ISBNコードが12桁 **/
    public function test_api_異常系_postで短いisbnのバリデーションエラーを返す()
    {
        $response = $this->json('POST', '/api/v1/books', [  // 書籍登録機能を呼び出す
            'title' => 'テストタイトル',
            'author' => 'テストユーザー',
            'isbn' => '123456789012',                   // digits:13なので12桁の文字列
            'published_date' => '2026-07-01',
            'description' => 'テスト説明',
            'image_url' => 'http://example.com/test.jpg',
            'user_id' => $this->user->id,               // 存在する user_id を使用
            'genres' => [$this->genre->id],             // 存在する genre_id を使用
        ]);

        $response->assertStatus(422)                        // バリデーションエラーは 422
            ->assertJsonValidationErrors(['isbn']);         // isbnに対するバリデーションエラーかを確認
    }

    /** ISBNコードが14桁 **/
    public function test_api_異常系_postで長いisbnのバリデーションエラーを返す()
    {
        $response = $this->json('POST', '/api/v1/books', [  // 書籍登録機能を呼び出す
            'title' => 'テストタイトル',
            'author' => 'テストユーザー',
            'isbn' => '12345678901234',                 // digits:13なので14桁の文字列
            'published_date' => '2026-07-01',
            'description' => 'テスト説明',
            'image_url' => 'http://example.com/test.jpg',
            'user_id' => $this->user->id,               // 存在する user_id を使用
            'genres' => [$this->genre->id],             // 存在する genre_id を使用
        ]);

        $response->assertStatus(422)                        // バリデーションエラーは 422
            ->assertJsonValidationErrors(['isbn']);         // isbnに対するバリデーションエラーかを確認
    }

    /** 同じISBNコード **/
    public function test_api_異常系_postで同じisbnのバリデーションエラーを返す()
    {
        $response = $this->actingAs($this->user)->
                 // すでに登録しているbooksレコードを取得
            json('GET', '/api/v1/books');

        $phpPayload = $response->json();                    // JSON形式からPHP配列にデコード

        $isbn = collect($phpPayload['data'])->first()['isbn'];  // ISBNコードを取得

        $response = $this->json('POST', '/api/v1/books', [  // 書籍登録機能を呼び出す
            'title' => 'テストタイトル',
            'author' => 'テストユーザー',
            'isbn' => $isbn,                            // 同じisbnをセット
            'published_date' => '2026-07-01',
            'description' => 'テスト説明',
            'image_url' => 'http://example.com/test.jpg',
            'user_id' => $this->user->id,               // 存在する user_id を使用
            'genres' => [$this->genre->id],             // 存在する genre_id を使用
        ]);
        // dd($response);
        $response->assertStatus(422)                        // バリデーションエラーは 422
            ->assertJsonValidationErrors(['isbn']);         // isbnに対するバリデーションエラーかを確認
    }

    /** 出版日が空文字列 **/
    public function test_api_異常系_postで空のpublished_dateのバリデーションエラーを返す()
    {
        $response = $this->json('POST', '/api/v1/books', [  // 書籍登録機能を呼び出す
            'title' => 'テストタイトル',
            'author' => 'テストユーザー',
            'isbn' => '1234567890123',
            'published_date' => '',                     // 出版日が空文字列
            'description' => 'テスト説明',
            'image_url' => 'http://example.com/test.jpg',
            'user_id' => $this->user->id,               // 存在する user_id を使用
            'genres' => [$this->genre->id],             // 存在する genre_id を使用
        ]);

        $response->assertStatus(422)                        // バリデーションエラーは 422
            ->assertJsonValidationErrors(['published_date']);  // published_dateに対するバリデーションエラーかを確認
    }

    /** 発行日が日付形式でない文字列 ＊*/
    public function test_api_異常系_postで不正な日付のバリデーションエラーを返す()
    {
        $response = $this->json('POST', '/api/v1/books', [  // 書籍登録機能を呼び出す
            'title' => 'テストタイトル',
            'author' => 'テストユーザー',
            'isbn' => '1234567890123',
            'published_date' => 'invalid_date',         // 日付形式でない
            'description' => 'テスト説明',
            'image_url' => 'http://example.com/test.jpg',
            'user_id' => $this->user->id,               // 存在する user_id を使用
            'genres' => [$this->genre->id],             // 存在する genre_id を使用
        ]);

        $response->assertStatus(422)                        // バリデーションエラーは 422
            ->assertJsonValidationErrors(['published_date']); // published_dateに対するバリデーションエラーか確認
    }

    /** 発行日が認識できない日付形式 */
    public function test_api_異常系_postで認識できない日付形式のバリデーションエラーを返す()
    {
        $response = $this->json('POST', '/api/v1/books', [  // 書籍登録機能を呼び出す
            'title' => 'テストタイトル',
            'author' => 'テストユーザー',
            'isbn' => '1234567890123',
            'published_date' => '2026年7月1日',         // 認識できない日付形式
            'description' => 'テスト説明',
            'image_url' => 'http://example.com/test.jpg',
            'user_id' => $this->user->id,               // 存在する user_id を使用
            'genres' => [$this->genre->id],             // 存在する genre_id を使用
        ]);

        $response->assertStatus(422)                        // バリデーションエラーは 422
            ->assertJsonValidationErrors(['published_date']); // published_dateに対するバリデーションエラーか確認
    }

    /** 長すぎる説明文
     * ※descriptionはnullable
     */
    public function test_api_異常系_postで長すぎる説明文のバリデーションエラーを返す()
    {
        $response = $this->json('POST', '/api/v1/books', [  // 書籍登録機能を呼び出す
            'title' => 'テストタイトル',
            'author' => 'テストユーザー',
            'isbn' => '1234567890123',
            'published_date' => '2026-07-01',
            'description' => str_repeat('A', 256),     // 無指定stringはmax:255なので256文字の文字列
            'image_url' => 'http://example.com/test.jpg',
            'user_id' => $this->user->id,               // 存在する user_id を使用
            'genres' => [$this->genre->id],             // 存在する genre_id を使用
        ]);

        $response->assertStatus(422)                        // バリデーションエラーは 422
            ->assertJsonValidationErrors(['description']);  // descriptionに対するバリデーションエラーかを確認
    }

    /** 不正なURL
     * ※image_urlはnullable
     */
    public function test_api_異常系_postで不正なurlのバリデーションエラーを返す()
    {
        $response = $this->json('POST', '/api/v1/books', [  // 書籍登録機能を呼び出す
            'title' => 'テストタイトル',
            'author' => 'テストユーザー',
            'isbn' => '1234567890123',
            'published_date' => '2026-07-01',
            'description' => 'テスト説明',
            'image_url' => 'invalid-url',               // URL形式でない
            'user_id' => $this->user->id,               // 存在する user_id を使用
            'genres' => [$this->genre->id],             // 存在する genre_id を使用
        ]);

        $response->assertStatus(422)                        // バリデーションエラーは 422
            ->assertJsonValidationErrors(['image_url']);  // image_urlに対するバリデーションエラーかを確認
    }

    /** ジャンルIDが空 **/
    public function test_api_異常系_postで空のgenresのバリデーションエラーを返す()
    {
        $response = $this->json('POST', '/api/v1/books', [  // 書籍登録機能を呼び出す
            'title' => 'テストタイトル',
            'author' => 'テストユーザー',
            'isbn' => '1234567890123',
            'published_date' => '2026-07-01',
            'description' => 'テスト説明',
            'image_url' => 'http://example.com/test.jpg',
            'user_id' => $this->user->id,               // 存在する user_id を使用
            'genres' => [],                             // genresが空配列
        ]);

        $response->assertStatus(422)                             // バリデーションエラーは 422
            ->assertJsonValidationErrors(['genres']);   // genresに対するバリデーションエラーかを確認
    }

    /** 存在しないジャンルID **/
    public function test_api_異常系_postで存在しないgenresのバリデーションエラーを返す()
    {
        $response = $this->json('POST', '/api/v1/books', [  // 書籍登録機能を呼び出す
            'title' => 'テストタイトル',
            'author' => 'テストユーザー',
            'isbn' => '1234567890123',
            'published_date' => '2026-07-01',
            'description' => 'テスト説明',
            'image_url' => 'http://example.com/test.jpg',
            'user_id' => $this->user->id,               // 存在する user_id を使用
            'genres' => [9999],                         // 存在しないジャンルID
        ]);

        $response->assertStatus(422)                        // バリデーションエラーは 422
            ->assertJsonValidationErrors(['genres.0']);  // genres配列に対するバリデーションエラーかを確認
    }

    /**----------------------------------------------------------------------------------------
     * PUT系テスト
     *---------------------------------------------------------------------------------------*/
    /** 正常終了 **/
    public function test_api_正常系_putで成功を返す()
    {
        $bookId = $this->book->id;                          // 登録済みの書籍IDを取得

        $response = $this->json('PUT', "/api/v1/books/{$bookId}", [  // 書籍更新機能を呼び出す
            'title' => 'テストタイトル',
            'author' => 'テストユーザー',
            'isbn' => '1234567890123',
            'published_date' => '2026-07-01',
            'description' => 'テスト説明',
            'image_url' => 'http://example.com/test.jpg',
            'user_id' => $this->user->id,               // 存在する user_id を使用
            'genres' => [$this->genre->id],             // 存在する genre_id を使用
        ]);

        // 検証
        $response->assertStatus(200);                       // 更新成功は 200

        $this->assertDatabaseHas('books', [                 // データベースに書き込まれているか確認
            'title' => 'テストタイトル',
            'isbn' => '1234567890123',
            'user_id' => $this->user->id,
        ]);
    }

    /** タイトルが空文字列 **/
    public function test_api_異常系_putで空のtitleのバリデーションエラーを返す()
    {
        $bookId = $this->book->id;                          // 登録済みの書籍IDを取得

        $response = $this->json('PUT', "/api/v1/books/{$bookId}", [  // 書籍更新機能を呼び出す
            'title' => '',                              // タイトルが空文字列
            'author' => 'テストユーザー',
            'isbn' => '1234567890123',
            'published_date' => '2026-07-01',
            'description' => 'テスト説明',
            'image_url' => 'http://example.com/test.jpg',
            'user_id' => $this->user->id,               // 存在する user_id を使用
            'genres' => [$this->genre->id],             // 存在する genre_id を使用
        ]);

        $response->assertStatus(422)                        // バリデーションエラーは 422
            ->assertJsonValidationErrors(['title']);        // titleに対するバリデーションエラーかを確認
    }

    /** 長すぎるタイトル文字列 **/
    public function test_api_異常系_putで長すぎるtitleのバリデーションエラーを返す()
    {
        $bookId = $this->book->id;                          // 登録済みの書籍IDを取得

        $response = $this->json('PUT', "/api/v1/books/{$bookId}", [  // 書籍更新機能を呼び出す
            'title' => str_repeat('A', 256),            // max:255なので256文字の文字列
            'author' => 'テストユーザー',
            'isbn' => '1234567890123',
            'published_date' => '2026-07-01',
            'description' => 'テスト説明',
            'image_url' => 'http://example.com/test.jpg',
            'user_id' => $this->user->id,               // 存在する user_id を使用
            'genres' => [$this->genre->id],             // 存在する genre_id を使用
        ]);

        $response->assertStatus(422)                        // バリデーションエラーは 422
            ->assertJsonValidationErrors(['title']);        // titleに対するバリデーションエラーかを確認
    }

    /** 著者が空文字列 **/
    public function test_api_異常系_putで空のauthorのバリデーションエラーを返す()
    {
        $bookId = $this->book->id;                          // 登録済みの書籍IDを取得

        $response = $this->json('PUT', "/api/v1/books/{$bookId}", [  // 書籍更新機能を呼び出す
            'title' => 'テストタイトル',
            'author' => '',                             // 著者が空文字列
            'isbn' => '1234567890123',
            'published_date' => '2026-07-01',
            'description' => 'テスト説明',
            'image_url' => 'http://example.com/test.jpg',
            'user_id' => $this->user->id,               // 存在する user_id を使用
            'genres' => [$this->genre->id],             // 存在する genre_id を使用
        ]);

        $response->assertStatus(422)                        // バリデーションエラーは 422
            ->assertJsonValidationErrors(['author']);       // authorに対するバリデーションエラーかを確認
    }

    /** 長すぎる著者文字列 **/
    public function test_api_異常系_putで長すぎるautherのバリデーションエラーを返す()
    {
        $bookId = $this->book->id;                          // 登録済みの書籍IDを取得

        $response = $this->json('PUT', "/api/v1/books/{$bookId}", [  // 書籍更新機能を呼び出す
            'title' => 'テストタイトル',
            'author' => str_repeat('A', 256),           // max:255なので256文字の文字列
            'isbn' => '1234567890123',
            'published_date' => '2026-07-01',
            'description' => 'テスト説明',
            'image_url' => 'http://example.com/test.jpg',
            'user_id' => $this->user->id,               // 存在する user_id を使用
            'genres' => [$this->genre->id],             // 存在する genre_id を使用
        ]);

        $response->assertStatus(422)                        // バリデーションエラーは 422
            ->assertJsonValidationErrors(['author']);       // authorに対するバリデーションエラーかを確認
    }

    /** ISBNコードが空文字列 **/
    public function test_api_異常系_putで空のisbnのバリデーションエラーを返す()
    {
        $bookId = $this->book->id;                          // 登録済みの書籍IDを取得

        $response = $this->json('PUT', "/api/v1/books/{$bookId}", [  // 書籍更新機能を呼び出す
            'title' => 'テストタイトル',
            'author' => 'テストユーザー',
            'isbn' => '',                               // isbnが空文字列
            'published_date' => '2026-07-01',
            'description' => 'テスト説明',
            'image_url' => 'http://example.com/test.jpg',
            'user_id' => $this->user->id,               // 存在する user_id を使用
            'genres' => [$this->genre->id],             // 存在する genre_id を使用
        ]);

        $response->assertStatus(422)                        // バリデーションエラーは 422
            ->assertJsonValidationErrors(['isbn']);         // isbnに対するバリデーションエラーかを確認
    }

    /** ISBNコードが12桁 **/
    public function test_api_異常系_putで短いisbnのバリデーションエラーを返す()
    {
        $bookId = $this->book->id;                          // 登録済みの書籍IDを取得

        $response = $this->json('PUT', "/api/v1/books/{$bookId}", [  // 書籍更新機能を呼び出す
            'title' => 'テストタイトル',
            'author' => 'テストユーザー',
            'isbn' => '123456789012',                   // digits:13なので12桁の文字列
            'published_date' => '2026-07-01',
            'description' => 'テスト説明',
            'image_url' => 'http://example.com/test.jpg',
            'user_id' => $this->user->id,               // 存在する user_id を使用
            'genres' => [$this->genre->id],             // 存在する genre_id を使用
        ]);

        $response->assertStatus(422)                        // バリデーションエラーは 422
            ->assertJsonValidationErrors(['isbn']);         // isbnに対するバリデーションエラーかを確認
    }

    /** ISBNコードが14桁 **/
    public function test_api_異常系_putで長いisbnのバリデーションエラーを返す()
    {
        $bookId = $this->book->id;                          // 登録済みの書籍IDを取得

        $response = $this->json('PUT', "/api/v1/books/{$bookId}", [  // 書籍更新機能を呼び出す
            'title' => 'テストタイトル',
            'author' => 'テストユーザー',
            'isbn' => '12345678901234',                 // digits:13なので14桁の文字列
            'published_date' => '2026-07-01',
            'description' => 'テスト説明',
            'image_url' => 'http://example.com/test.jpg',
            'user_id' => $this->user->id,               // 存在する user_id を使用
            'genres' => [$this->genre->id],             // 存在する genre_id を使用
        ]);

        $response->assertStatus(422)                        // バリデーションエラーは 422
            ->assertJsonValidationErrors(['isbn']);         // isbnに対するバリデーションエラーかを確認
    }

    /** 出版日が空文字列 **/
    public function test_api_異常系_putで空のpublished_dateのバリデーションエラーを返す()
    {
        $bookId = $this->book->id;                          // 登録済みの書籍IDを取得

        $response = $this->json('PUT', "/api/v1/books/{$bookId}", [  // 書籍更新機能を呼び出す
            'title' => 'テストタイトル',
            'author' => 'テストユーザー',
            'isbn' => '1234567890123',
            'published_date' => '',                     // 出版日が空文字列
            'description' => 'テスト説明',
            'image_url' => 'http://example.com/test.jpg',
            'user_id' => $this->user->id,               // 存在する user_id を使用
            'genres' => [$this->genre->id],             // 存在する genre_id を使用
        ]);

        $response->assertStatus(422)                        // バリデーションエラーは 422
            ->assertJsonValidationErrors(['published_date']);  // published_dateに対するバリデーションエラーかを確認
    }

    /** 発行日が日付形式でない文字列 ＊*/
    public function test_api_異常系_putで不正な日付のバリデーションエラーを返す()
    {
        $bookId = $this->book->id;                          // 登録済みの書籍IDを取得

        $response = $this->json('PUT', "/api/v1/books/{$bookId}", [  // 書籍更新機能を呼び出す
            'title' => 'テストタイトル',
            'author' => 'テストユーザー',
            'isbn' => '1234567890123',
            'published_date' => 'invalid_date',         // 日付形式でない
            'description' => 'テスト説明',
            'image_url' => 'http://example.com/test.jpg',
            'user_id' => $this->user->id,               // 存在する user_id を使用
            'genres' => [$this->genre->id],             // 存在する genre_id を使用
        ]);

        $response->assertStatus(422)                        // バリデーションエラーは 422
            ->assertJsonValidationErrors(['published_date']); // published_dateに対するバリデーションエラーか確認
    }

    /** 発行日が認識できない日付形式 */
    public function test_api_異常系_putで認識できない日付形式のバリデーションエラーを返す()
    {
        $bookId = $this->book->id;                          // 登録済みの書籍IDを取得

        $response = $this->json('PUT', "/api/v1/books/{$bookId}", [  // 書籍更新機能を呼び出す
            'title' => 'テストタイトル',
            'author' => 'テストユーザー',
            'isbn' => '1234567890123',
            'published_date' => '2026年7月1日',         // 認識できない日付形式
            'description' => 'テスト説明',
            'image_url' => 'http://example.com/test.jpg',
            'user_id' => $this->user->id,               // 存在する user_id を使用
            'genres' => [$this->genre->id],             // 存在する genre_id を使用
        ]);

        $response->assertStatus(422)                        // バリデーションエラーは 422
            ->assertJsonValidationErrors(['published_date']); // published_dateに対するバリデーションエラーか確認
    }

    /** 長すぎる説明文
     * ※descriptionはnullable
     */
    public function test_api_異常系_putで長すぎる説明文のバリデーションエラーを返す()
    {
        $bookId = $this->book->id;                          // 登録済みの書籍IDを取得

        $response = $this->json('PUT', "/api/v1/books/{$bookId}", [  // 書籍更新機能を呼び出す
            'title' => 'テストタイトル',
            'author' => 'テストユーザー',
            'isbn' => '1234567890123',
            'published_date' => '2026-07-01',
            'description' => str_repeat('A', 256),     // 無指定stringはmax:255なので256文字の文字列
            'image_url' => 'http://example.com/test.jpg',
            'user_id' => $this->user->id,               // 存在する user_id を使用
            'genres' => [$this->genre->id],             // 存在する genre_id を使用
        ]);

        $response->assertStatus(422)                        // バリデーションエラーは 422
            ->assertJsonValidationErrors(['description']);  // descriptionに対するバリデーションエラーかを確認
    }

    /** 不正なURL
     * ※image_urlはnullable
     */
    public function test_api_異常系_putで不正な_ur_lのバリデーションエラーを返す()
    {
        $bookId = $this->book->id;                          // 登録済みの書籍IDを取得

        $response = $this->json('PUT', "/api/v1/books/{$bookId}", [  // 書籍更新機能を呼び出す
            'title' => 'テストタイトル',
            'author' => 'テストユーザー',
            'isbn' => '1234567890123',
            'published_date' => '2026-07-01',
            'description' => 'テスト説明',
            'image_url' => 'invalid-url',               // URL形式でない
            'user_id' => $this->user->id,               // 存在する user_id を使用
            'genres' => [$this->genre->id],             // 存在する genre_id を使用
        ]);

        $response->assertStatus(422)                        // バリデーションエラーは 422
            ->assertJsonValidationErrors(['image_url']);  // image_urlに対するバリデーションエラーかを確認
    }

    /** ジャンルIDが空 **/
    public function test_api_異常系_putで空のgenresのバリデーションエラーを返す()
    {
        $bookId = $this->book->id;                          // 登録済みの書籍IDを取得

        $response = $this->json('PUT', "/api/v1/books/{$bookId}", [  // 書籍更新機能を呼び出す
            'title' => 'テストタイトル',
            'author' => 'テストユーザー',
            'isbn' => '1234567890123',
            'published_date' => '2026-07-01',
            'description' => 'テスト説明',
            'image_url' => 'http://example.com/test.jpg',
            'user_id' => $this->user->id,               // 存在する user_id を使用
            'genres' => [],                             // genresが空配列
        ]);

        $response->assertStatus(422)                             // バリデーションエラーは 422
            ->assertJsonValidationErrors(['genres']);   // genresに対するバリデーションエラーかを確認
    }

    /** 存在しないジャンルID **/
    public function test_api_異常系_putで存在しないgenresのバリデーションエラーを返す()
    {
        $bookId = $this->book->id;                          // 登録済みの書籍IDを取得

        $response = $this->json('PUT', "/api/v1/books/{$bookId}", [  // 書籍更新機能を呼び出す
            'title' => 'テストタイトル',
            'author' => 'テストユーザー',
            'isbn' => '1234567890123',
            'published_date' => '2026-07-01',
            'description' => 'テスト説明',
            'image_url' => 'http://example.com/test.jpg',
            'user_id' => $this->user->id,               // 存在する user_id を使用
            'genres' => [9999],                         // 存在しないジャンルID
        ]);

        $response->assertStatus(422)                        // バリデーションエラーは 422
            ->assertJsonValidationErrors(['genres.0']);  // genres配列に対するバリデーションエラーかを確認
    }
}
