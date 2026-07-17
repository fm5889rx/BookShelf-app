<?php

namespace Tests\Unit;

use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * 書籍情報のバリデーションチェック単体テスト
 */
class BookValidationTest extends TestCase
{
    use RefreshDatabase;                                    // データベースのリフレッシュを行うトレイトを使用

    protected $user;                                        // テストで使用するユーザー情報

    protected $genres;                                      // テストで使用するジャンルデータ

    protected $genreIds;                                    // ジャンルID配列

    protected $validData;                                   // テストで使用する書籍情報（正常値）

    /**
     * テスト前の共通処理
     * 正常値を保存
     * 各テストでは異常値だけ書き換えるようにする
     */
    protected function setUp(): void
    {
        parent::setUp();                                    // 親クラスのセットアップ

        $this->user = User::factory()->create();            // テスト用ユーザーを作成

        $this->genres = Genre::factory()->count(1)->create();  // テスト用のジャンルレコードを1件生成

        $this->genreIds = $this->genres->pluck('id')->toArray();  // ジャンルレコードのidだけを配列として格納

        $this->validData = Book::factory()->create();       // 1件の書籍情報を作成（正常系）

        $this->validData['isbn'] = '1234567890123';         // isbnの重複回避で書き換え

        $this->validData['genres'] = $this->genreIds;       // ジャンル配列を書籍情報に追加
    }

    /**-----------------------------------------------------
     * 書籍新規作成（store）バリデーションチェック
     *----------------------------------------------------*/
    /** 正常値を渡す **/
    public function test_書籍新規作成_バリデーションチェック_正常系()
    {
        // 準備
        $rules = (new StoreBookRequest)->rules();           // バリデーションルールを取得する

        // 実行
        $validator = Validator::make($this->validData->toArray(), $rules);  // バリデーションチェック

        // 判定
        $this->assertTrue($validator->passes());            // 成功ならTrueを返す
    }

    /**
     * タイトル名（title）関係
     */
    /** タイトル名が無い **/
    public function test_書籍新規作成_書籍タイトルが無い()
    {
        // 準備
        $rules = (new StoreBookRequest)->rules();           // バリデーションルールを取得する

        $this->validData['title'] = '';                     // タイトルが空文字列

        // 実行
        $validator = Validator::make($this->validData->toArray(), $rules);  // バリデーションチェック

        // 判定
        $this->assertTrue($validator->fails());             // 失敗ならTrueを返す
    }

    /** タイトル名が長すぎる **/
    public function test_書籍新規作成_書籍タイトルが長すぎる()
    {
        // 準備
        $rules = (new StoreBookRequest)->rules();           // バリデーションルールを取得する

        $this->validData['title'] = str_repeat('A', 256);   // タイトルが256文字の文字列

        // 実行
        $validator = Validator::make($this->validData->toArray(), $rules);  // バリデーションチェック

        // 判定
        $this->assertTrue($validator->fails());             // 失敗ならTrueを返す
    }

    /**
     * 著者（author）関係
     */
    /** 著者が無い **/
    public function test_書籍新規作成_著者が無い()
    {
        // 準備
        $rules = (new StoreBookRequest)->rules();           // バリデーションルールを取得する

        $this->validData['author'] = '';                    // 著者が空文字列

        // 実行
        $validator = Validator::make($this->validData->toArray(), $rules);  // バリデーションチェック

        // 判定
        $this->assertTrue($validator->fails());             // 失敗ならTrueを返す
    }

    /** 著者が長すぎる **/
    public function test_書籍新規作成_著者が長すぎる()
    {
        // 準備
        $rules = (new StoreBookRequest)->rules();           // バリデーションルールを取得する

        $this->validData['author'] = str_repeat('A', 256);  // 著者が256文字の文字列

        // 実行
        $validator = Validator::make($this->validData->toArray(), $rules);  // バリデーションチェック

        // 判定
        $this->assertTrue($validator->fails());             // 失敗ならTrueを返す
    }

    /**
     * ISBNコード（isbn）関係
     */
    /** ISBNコードが無い **/
    public function test_書籍新規作成_isb_nが無い()
    {
        // 準備
        $rules = (new StoreBookRequest)->rules();           // バリデーションルールを取得する

        $this->validData['isbn'] = '';                      // ISBNコードが空文字列

        // 実行
        $validator = Validator::make($this->validData->toArray(), $rules);  // バリデーションチェック

        // 判定
        $this->assertTrue($validator->fails());             // 失敗ならTrueを返す
    }

    /** ISBNコードが13桁でない **/
    public function test_書籍新規作成_isb_nが12桁()
    {
        // 準備
        $rules = (new StoreBookRequest)->rules();           // バリデーションルールを取得する

        $this->validData['isbn'] = str_repeat('1', 12);     // ISBNコードが12桁の文字列

        // 実行
        $validator = Validator::make($this->validData->toArray(), $rules);  // バリデーションチェック

        // 判定
        $this->assertTrue($validator->fails());             // 失敗ならTrueを返す
    }

    /** ISBNコードが13桁でない **/
    public function test_書籍新規作成_isb_nが14桁()
    {
        // 準備
        $rules = (new StoreBookRequest)->rules();           // バリデーションルールを取得する

        $this->validData['isbn'] = str_repeat('1', 14);     // ISBNコードが12桁の文字列

        // 実行
        $validator = Validator::make($this->validData->toArray(), $rules);  // バリデーションチェック

        // 判定
        $this->assertTrue($validator->fails());             // 失敗ならTrueを返す
    }

    /** ISBNコードがユニークでない **/
    public function test_書籍新規作成_isb_nがユニークでない()
    {
        // 準備
        $rules = (new StoreBookRequest)->rules();           // バリデーションルールを取得する

        $book = Book::factory()->create();                  // 2件目の書籍情報を作成

        $book['isbn'] = '1234567890123';                    // 1件目と同じISBNコードにする

        // 実行
        $validator = Validator::make($book->toArray(), $rules);  // バリデーションチェック

        // 判定
        $this->assertTrue($validator->fails());             // 失敗ならTrueを返す
    }

    /**
     * 出版日（published_date）関係
     */
    /** 出版日が無い **/
    public function test_書籍新規作成_出版日が無い()
    {
        // 準備
        $rules = (new StoreBookRequest)->rules();           // バリデーションルールを取得する

        $this->validData['published_date'] = '';            // 出版日が空文字列

        // 実行
        $validator = Validator::make($this->validData->toArray(), $rules);  // バリデーションチェック

        // 判定
        $this->assertTrue($validator->fails());             // 失敗ならTrueを返す
    }

    /** 出版日が有効な日付形式でない **/
    public function test_書籍新規作成_出版日が有効な日付形式でない()
    {
        // 準備
        $rules = (new StoreBookRequest)->rules();           // バリデーションルールを取得する

        $this->validData['published_date'] = '2026年7月5日';  // 出版日が有効な日付形式でない

        // 実行
        $validator = Validator::make($this->validData->toArray(), $rules);  // バリデーションチェック

        // 判定
        $this->assertTrue($validator->fails());             // 失敗ならTrueを返す
    }

    /**
     * 画像URL（image_url）関係
     * ※image_urlはtext/nullable
     */
    /** 画像URLが長すぎる **/
    public function test_書籍新規作成_画像urlが長すぎる()
    {
        // 準備
        $rules = (new StoreBookRequest)->rules();           // バリデーションルールを取得する

        $this->validData['image_url'] = 'http://'.str_repeat('A', 249);  // 画像URLがURL形式で256文字の文字列

        // 実行
        $validator = Validator::make($this->validData->toArray(), $rules);  // バリデーションチェック

        // 判定
        $this->assertTrue($validator->fails());             // 失敗ならTrueを返す
    }

    /** 画像URLがURL形式でない **/
    public function test_書籍新規作成_画像urlがurl形式でない()
    {
        // 準備
        $rules = (new StoreBookRequest)->rules();           // バリデーションルールを取得する

        $this->validData['image_url'] = str_repeat('A', 255);  // 画像URLがURL形式でない255文字の文字列

        // 実行
        $validator = Validator::make($this->validData->toArray(), $rules);  // バリデーションチェック

        // 判定
        $this->assertTrue($validator->fails());             // 失敗ならTrueを返す
    }

    public function test_書籍新規作成_ジャンル指定無し()
    {
        // 準備
        $rules = (new StoreBookRequest)->rules();           // バリデーションルールを取得する

        $this->validData['genres'] = [];                    // ジャンル配列が空

        // 実行
        $validator = Validator::make($this->validData->toArray(), $rules);  // バリデーションチェック

        // 判定
        $this->assertTrue($validator->fails());             // 失敗ならTrueを返す

    }

    /**-----------------------------------------------------
     * 書籍更新（update）バリデーションチェック
     *----------------------------------------------------*/
    /** 正常値を渡す **/
    public function test_書籍更新_バリデーションチェック_正常系()
    {
        // 準備
        $rules = (new UpdateBookRequest)->rules();          // バリデーションルールを取得する

        // 実行
        $validator = Validator::make($this->validData->toArray(), $rules);  // バリデーションチェック

        // 判定
        $this->assertTrue($validator->passes());            // 成功ならTrueを返す
    }

    /**
     * タイトル名（title）関係
     */
    /** タイトル名が無い **/
    public function test_書籍更新_書籍タイトルが無い()
    {
        // 準備
        $rules = (new UpdateBookRequest)->rules();          // バリデーションルールを取得する

        $this->validData['title'] = '';                     // タイトルが空文字列

        // 実行
        $validator = Validator::make($this->validData->toArray(), $rules);  // バリデーションチェック

        // 判定
        $this->assertTrue($validator->fails());             // 失敗ならTrueを返す
    }

    /** タイトル名が長すぎる **/
    public function test_書籍更新_書籍タイトルが長すぎる()
    {
        // 準備
        $rules = (new UpdateBookRequest)->rules();          // バリデーションルールを取得する

        $this->validData['title'] = str_repeat('A', 256);   // タイトルが256文字の文字列

        // 実行
        $validator = Validator::make($this->validData->toArray(), $rules);  // バリデーションチェック

        // 判定
        $this->assertTrue($validator->fails());             // 失敗ならTrueを返す
    }

    /**
     * 著者（author）関係
     */
    /** 著者が無い **/
    public function test_書籍更新_著者が無い()
    {
        // 準備
        $rules = (new UpdateBookRequest)->rules();          // バリデーションルールを取得する

        $this->validData['author'] = '';                    // 著者が空文字列

        // 実行
        $validator = Validator::make($this->validData->toArray(), $rules);  // バリデーションチェック

        // 判定
        $this->assertTrue($validator->fails());             // 失敗ならTrueを返す
    }

    /** 著者が長すぎる **/
    public function test_書籍更新_著者が長すぎる()
    {
        // 準備
        $rules = (new UpdateBookRequest)->rules();          // バリデーションルールを取得する

        $this->validData['author'] = str_repeat('A', 256);  // 著者が256文字の文字列

        // 実行
        $validator = Validator::make($this->validData->toArray(), $rules);  // バリデーションチェック

        // 判定
        $this->assertTrue($validator->fails());             // 失敗ならTrueを返す
    }

    /**
     * ISBNコード（isbn）関係
     */
    /** ISBNコードが無い **/
    public function test_書籍更新_isb_nが無い()
    {
        // 準備
        $rules = (new UpdateBookRequest)->rules();          // バリデーションルールを取得する

        $this->validData['isbn'] = '';                      // ISBNコードが空文字列

        // 実行
        $validator = Validator::make($this->validData->toArray(), $rules);  // バリデーションチェック

        // 判定
        $this->assertTrue($validator->fails());             // 失敗ならTrueを返す
    }

    /** ISBNコードが13桁でない **/
    public function test_書籍更新_isb_nが12桁()
    {
        // 準備
        $rules = (new UpdateBookRequest)->rules();          // バリデーションルールを取得する

        $this->validData['isbn'] = str_repeat('1', 12);     // ISBNコードが12桁の文字列

        // 実行
        $validator = Validator::make($this->validData->toArray(), $rules);  // バリデーションチェック

        // 判定
        $this->assertTrue($validator->fails());             // 失敗ならTrueを返す
    }

    /** ISBNコードが13桁でない **/
    public function test_書籍更新_isb_nが14桁()
    {
        // 準備
        $rules = (new UpdateBookRequest)->rules();          // バリデーションルールを取得する

        $this->validData['isbn'] = str_repeat('1', 14);     // ISBNコードが12桁の文字列

        // 実行
        $validator = Validator::make($this->validData->toArray(), $rules);  // バリデーションチェック

        // 判定
        $this->assertTrue($validator->fails());             // 失敗ならTrueを返す
    }

    /**
     * 出版日（published_date）関係
     */
    /** 出版日が無い **/
    public function test_書籍更新_出版日が無い()
    {
        // 準備
        $rules = (new UpdateBookRequest)->rules();          // バリデーションルールを取得する

        $this->validData['published_date'] = '';            // 出版日が空文字列

        // 実行
        $validator = Validator::make($this->validData->toArray(), $rules);  // バリデーションチェック

        // 判定
        $this->assertTrue($validator->fails());             // 失敗ならTrueを返す
    }

    /** 出版日が有効な日付形式でない **/
    public function test_書籍更新_出版日が有効な日付形式でない()
    {
        // 準備
        $rules = (new UpdateBookRequest)->rules();          // バリデーションルールを取得する

        $this->validData['published_date'] = '2026年7月5日';  // 出版日が有効な日付形式でない

        // 実行
        $validator = Validator::make($this->validData->toArray(), $rules);  // バリデーションチェック

        // 判定
        $this->assertTrue($validator->fails());             // 失敗ならTrueを返す
    }

    /**
     * 画像URL（image_url）関係
     * ※image _urlはtext/nullable
     */
    /** 画像URLが長すぎる **/
    public function test_書籍更新_画像urlが長すぎる()
    {
        // 準備
        $rules = (new UpdateBookRequest)->rules();          // バリデーションルールを取得する

        $this->validData['image_url'] = 'http://'.str_repeat('A', 249);  // 画像URLがURL形式で256文字の文字列

        // 実行
        $validator = Validator::make($this->validData->toArray(), $rules);  // バリデーションチェック

        // 判定
        $this->assertTrue($validator->fails());             // 失敗ならTrueを返す
    }

    /** 画像URLがURL形式でない **/
    public function test_書籍更新_画像urlが_url形式でない()
    {
        // 準備
        $rules = (new UpdateBookRequest)->rules();          // バリデーションルールを取得する

        $this->validData['image_url'] = str_repeat('A', 255);  // 画像URLがURL形式でない255文字の文字列

        // 実行
        $validator = Validator::make($this->validData->toArray(), $rules);  // バリデーションチェック

        // 判定
        $this->assertTrue($validator->fails());             // 失敗ならTrueを返す
    }

    public function test_書籍更新_ジャンル指定無し()
    {
        // 準備
        $rules = (new UpdateBookRequest)->rules();          // バリデーションルールを取得する

        $this->validData['genres'] = [];                    // ジャンル配列が空

        // 実行
        $validator = Validator::make($this->validData->toArray(), $rules);  // バリデーションチェック

        // 判定
        $this->assertTrue($validator->fails());             // 失敗ならTrueを返す

    }
}
