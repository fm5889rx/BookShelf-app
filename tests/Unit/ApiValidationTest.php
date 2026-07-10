<?php

namespace Tests\Unit;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Validation\Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiValidationTest extends TestCase
{
    /** テスト共有変数 */
    protected $userId;                                  // テスト用に保持するユーザーID

    protected $genreId;                                 // テスト用に保持するジャンルID

    protected $book;                                    // テスト用に保持する書籍情報

    protected function setUp(): void
    {
        parent::setUp();

        // テスト用のusersデータを作成
        $user = User::factory()->create();              // テスト用のユーザーを登録
        $this->userId = $user->id;                      // 取得したIDを保持

        // テスト用のtagsテーブルを作成
        $genre = Genre::factory()->create();            // テスト用のジャンルを登録
        $this->genreId = $genre->id;                    // 取得したIDを保持

        $book = Book::factory()->count(1)->create([     // テスト用の書籍情報を1件登録
            'user_id' => $this->userId,                 // 存在するユーザーIDを使用
        ]);
    }

    /**
     * GET系テスト
     */
    /** @test */
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
    public function test_api_異常系_ジャンルIDが存在しない()
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
        $response = $this->json('POST', '/api/v1/books', [
            'title' => 'テストタイトル',
            'author' => 'テストユーザー',
            'isbn' => '1234567890123',
            'published_date' => '2026-07-01',
            'description' => 'テスト説明',
            'image_url' => 'http://example.com/test.jpg',
            'user_id' => $this->userId,                 // 存在する user_id を使用
            'genres' => [$this->genreId],               // 存在する genre_id を使用
        ]);

        $response
            ->assertStatus(201);                        // 作成成功は 201
    }

    /** タイトルが空文字列 **/
    public function test_api_異常系_postで空のtitleのバリデーションエラーを返す()
    {
        $response = $this->json('POST', '/api/v1/books', [
            'title' => '',                             // タイトルが空文字列
            'author' => 'テストユーザー',
            'isbn' => '1234567890123',
            'published_date' => '2026-07-01',
            'description' => 'テスト説明',
            'image_url' => 'http://example.com/test.jpg',
            'genres' => [$this->genreId],
        ]);

        $response
            ->assertStatus(422)                         // バリデーションエラーは 422
            ->assertJsonValidationErrors(['title']);    // titleに対するバリデーションエラーかを確認
    }

    /** 長すぎるタイトル文字列 **/
    public function test_api_異常系_postで長すぎるtitleのバリデーションエラーを返す()
    {
        $response = $this->json('POST', '/api/v1/books', [
            'title' => str_repeat('A', 256),            // mox:255なので256文字の文字列
            'author' => 'テストユーザー',
            'isbn' => '1234567890123',
            'published_date' => '2026-07-01',
            'description' => 'テスト説明',
            'image_url' => 'http://example.com/test.jpg',
            'genres' => [$this->genreId],
        ]);

        $response
            ->assertStatus(422)                         // バリデーションエラーは 422
            ->assertJsonValidationErrors(['title']);    // titleに対するバリデーションエラーかを確認
    }

    /** 著者が空文字列 **/
    public function test_api_異常系_postで空のauthorのバリデーションエラーを返す()
    {
        $response = $this->json('POST', '/api/v1/books', [
            'title' => 'テストタイトル',
            'author' => '',                             // 著者が空文字列
            'isbn' => '1234567890123',
            'published_date' => '2026-07-01',
            'description' => 'テスト説明',
            'image_url' => 'http://example.com/test.jpg',
            'genres' => [$this->genreId],
        ]);

        $response
            ->assertStatus(422)                         // バリデーションエラーは 422
            ->assertJsonValidationErrors(['author']);   // authorに対するバリデーションエラーかを確認
    }

    /** 長すぎる著者文字列 **/
    public function test_api_異常系_postで長すぎるautherのバリデーションエラーを返す()
    {
        $response = $this->json('POST', '/api/v1/books', [
            'title' => 'テストタイトル',
            'author' => str_repeat('A', 256),           // max:255なので256文字の文字列
            'isbn' => '1234567890123',
            'published_date' => '2026-07-01',
            'description' => 'テスト説明',
            'image_url' => 'http://example.com/test.jpg',
            'genres' => [$this->genreId],
        ]);

        $response
            ->assertStatus(422)                         // バリデーションエラーは 422
            ->assertJsonValidationErrors(['author']);   // authorに対するバリデーションエラーかを確認
    }

    /** ISBNコードが空文字列 **/
    public function test_api_異常系_postで空のisbnのバリデーションエラーを返す()
    {
        $response = $this->json('POST', '/api/v1/books', [
            'title' => 'テストタイトル',
            'author' => 'テストユーザー',
            'isbn' => '',                               // isbnが空文字列
            'published_date' => '2026-07-01',
            'description' => 'テスト説明',
            'image_url' => 'http://example.com/test.jpg',
            'genres' => [$this->genreId],
        ]);

        $response
            ->assertStatus(422)                         // バリデーションエラーは 422
            ->assertJsonValidationErrors(['isbn']);     // isbnに対するバリデーションエラーかを確認
    }

    /** ISBNコードが12桁 **/
    public function test_api_異常系_postで短いisbnのバリデーションエラーを返す()
    {
        $response = $this->json('POST', '/api/v1/books', [
            'title' => 'テストタイトル',
            'author' => 'テストユーザー',
            'isbn' => '123456789012',                   // digits:13なので12桁の文字列
            'published_date' => '2026-07-01',
            'description' => 'テスト説明',
            'image_url' => 'http://example.com/test.jpg',
            'genres' => [$this->genreId],
        ]);

        $response
            ->assertStatus(422)                         // バリデーションエラーは 422
            ->assertJsonValidationErrors(['isbn']);     // isbnに対するバリデーションエラーかを確認
    }

    /** ISBNコードが14桁 **/
    public function test_api_異常系_postで長いisbnのバリデーションエラーを返す()
    {
        $response = $this->json('POST', '/api/v1/books', [
            'title' => 'テストタイトル',
            'author' => 'テストユーザー',
            'isbn' => '12345678901234',                 // digits:13なので14桁の文字列
            'published_date' => '2026-07-01',
            'description' => 'テスト説明',
            'image_url' => 'http://example.com/test.jpg',
            'genres' => [$this->genreId],
        ]);

        $response
            ->assertStatus(422)                         // バリデーションエラーは 422
            ->assertJsonValidationErrors(['isbn']);     // isbnに対するバリデーションエラーかを確認
    }

    /** 発行日が空文字列 **/
    public function test_api_異常系_postで空のpublished_dateのバリデーションエラーを返す()
    {
        $response = $this->json('POST', '/api/v1/books', [
            'title' => 'テストタイトル',
            'author' => 'テストユーザー',
            'isbn' => '1234567890123',
            'published_date' => '',                     // 発行日が空文字列
            'description' => 'テスト説明',
            'image_url' => 'http://example.com/test.jpg',
            'genres' => [$this->genreId],
        ]);

        $response
            ->assertStatus(422)                         // バリデーションエラーは 422
            ->assertJsonValidationErrors(['isbn']);     // isbnに対するバリデーションエラーかを確認
    }

    /** 発行日が日付形式でない文字列 ＊*/
    public function test_api_異常系_postで不正な日付のバリデーションエラーを返す()
    {
        $response = $this->json('POST', '/api/v1/books', [
            'title' => 'テストタイトル',
            'author' => 'テストユーザー',
            'isbn' => '1234567890123',
            'published_date' => 'invalid_date',         // 日付形式でない
            'description' => 'テスト説明',
            'image_url' => 'http://example.com/test.jpg',
            'genres' => [$this->genreId],
        ]);

        $response
            ->assertStatus(422)                         // バリデーションエラーは 422
            ->assertJsonValidationErrors(['published_date']); // published_dateに対するバリデーションエラーか確認
    }

    /** 発行日が認識できない日付形式 */
    public function test_api_異常系_postで認識できない日付形式のバリデーションエラーを返す()
    {
        $response = $this->json('POST', '/api/v1/books', [
            'title' => 'テストタイトル',
            'author' => 'テストユーザー',
            'isbn' => '1234567890123',
            'published_date' => '2026年7月1日',          // 認識できない日付形式
            'description' => 'テスト説明',
            'image_url' => 'http://example.com/test.jpg',
            'genres' => [$this->genreId],
        ]);

        $response
            ->assertStatus(422)                         // バリデーションエラーは 422
            ->assertJsonValidationErrors(['published_date']); // published_dateに対するバリデーションエラーか確認
    }

    /** 長すぎる説明文
     * ※descriptionはnullable
     */
    public function test_api_異常系_postで長すぎる説明文のバリデーションエラーを返す()
    {
        $response = $this->json('POST', '/api/v1/books', [
            'title' => 'テストタイトル',
            'author' => 'テストユーザー',
            'isbn' => '1234567890123',
            'published_date' => '2026-07-01',
            'description' => str_repeat('A', 65536),    // text型はmax:65535なので65536文字の文字列
            'image_url' => 'http://example.com/test.jpg',
            'genres' => [$this->genreId],
        ]);

        $response
            ->assertStatus(422)                         // バリデーションエラーは 422
            ->assertJsonValidationErrors(['description']);  // descriptionに対するバリデーションエラーかを確認
    }

    /** 不正なURL
     * ※image_urlはnullable
     */
    public function test_api_異常系_postで不正なURLのバリデーションエラーを返す()
    {
        $response = $this->json('POST', '/api/v1/books', [
            'title' => 'テストタイトル',
            'author' => 'テストユーザー',
            'isbn' => '1234567890123',
            'published_date' => '2026-07-01',
            'description' => 'テスト説明',
            'image_url' => 'invalid-url',               // URL形式でない
            'genres' => [$this->genreId],
        ]);

        $response
            ->assertStatus(422)                         // バリデーションエラーは 422
            ->assertJsonValidationErrors(['image_url']);  // image_urlに対するバリデーションエラーかを確認
    }

    /** ジャンルIDが空 **/
    public function test_api_異常系_postで空のgenresのバリデーションエラーを返す()
    {
        $response = $this->json('POST', '/api/v1/books', [
            'title' => 'テストタイトル',
            'author' => 'テストユーザー',
            'isbn' => '1234567890123',
            'published_date' => '2026-07-01',
            'description' => 'テスト説明',
            'image_url' => 'http://example.com/test.jpg',
            'genres' => [],                             // genresが空配列
        ]);

        $response
            ->assertStatus(422)                         // バリデーションエラーは 422
            ->assertJsonValidationErrors(['genrea']);   // genresに対するバリデーションエラーかを確認
    }

    /** 存在しないジャンルID **/
    public function test_api_異常系_postで存在しないgenresのバリデーションエラーを返す()
    {
        $response = $this->json('POST', '/api/v1/books', [
            'title' => 'テストタイトル',
            'author' => 'テストユーザー',
            'isbn' => '1234567890123',
            'published_date' => '2026-07-01',
            'description' => 'テスト説明',
            'image_url' => 'http://example.com/test.jpg',
            'genres' => [9999],                         // 存在しないジャンルID
        ]);

        $response
            ->assertStatus(422)                         // バリデーションエラーは 422
            ->assertJsonValidationErrors(['genres']);   // genresに対するバリデーションエラーかを確認
    }
}
