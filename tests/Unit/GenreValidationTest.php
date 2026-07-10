<?php

namespace Tests\Unit;

use App\Models\Genre;
use App\Http\Requests\StoreGenreRequest;
use App\Http\Requests\UpdateGenreRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * ジャンルのバリデーションチェック単体テスト
 */

class GenreValidationTest extends TestCase
{
    use RefreshDatabase;                                    // データベースのリフレッシュを行うトレイトを使用

    protected $book;                                        // テストで使用する書籍情報

    protected $genres;                                      // テストで使用するジャンルデータ

    protected $bookIds;                                     // ジャンルID配列

    protected $validData;                                   // テストで使用する書籍情報（正常値）

     /**-------------------------------------------------------------------------------------------------
     * ジャンル新規作成（store）バリデーションチェック
     *------------------------------------------------------------------------------------------------*/
    /** 正常値を渡す **/
    public function test_ジャンル新規作成_バリデーションチェック_正常系()
    {
        // 準備
        $this->validData = Genre::factory()->make();        // テスト用のジャンルレコードを1件生成

        $rules = (new StoreGenreRequest)->rules();          // バリデーションルールを取得する

        // 実行
        $validator = Validator::make($this->validData->toArray(), $rules);  // バリデーションチェック

        // 判定
        $this->assertTrue($validator->passes());            // 成功ならTrueを返す
    }

    /**
     * ジャンル名（name）関係
     */
    /** ジャンル名が無い **/
    public function test_ジャンル新規作成_ジャンル名が無い()
    {
        // 準備
        $this->validData = Genre::factory()->make();        // テスト用のジャンルレコードを1件生成

        $rules = (new StoreGenreRequest)->rules();          // バリデーションルールを取得する

        $this->validData['name'] = '';                      // ジャンル名が空文字列

        // 実行
        $validator = Validator::make($this->validData->toArray(), $rules);  // バリデーションチェック

        // 判定
        $this->assertTrue($validator->fails());             // 失敗ならTrueを返す
    }

    /** ジャンル名が長すぎる **/
    public function test_ジャンル新規作成_ジャンル名が長すぎる()
    {
        // 準備
        $this->validData = Genre::factory()->make();        // テスト用のジャンルレコードを1件生成

        $rules = (new StoreGenreRequest)->rules();          // バリデーションルールを取得する

        $this->validData['name'] = str_repeat('A', 51);     // ジャンル名が51文字の文字列

        // 実行
        $validator = Validator::make($this->validData->toArray(), $rules);  // バリデーションチェック

        // 判定
        $this->assertTrue($validator->fails());             // 失敗ならTrueを返す
    }

    /** ジャンル名がユニークでない **/
    public function test_ジャンル新規作成_ジャンル名がユニークでない()
    {
        // 準備
        $this->validData = Genre::factory()->create();      // テスト用のジャンルレコードを1件生成

        $rules = (new StoreGenreRequest)->rules();          // バリデーションルールを取得する

        $genre = Genre::factory()->create();                // 2件目のジャンルを作成

        $genre['name'] = $this->validData['name'];          // 1件目と同じジャンル名にする

        // 実行
        $validator = Validator::make($genre->toArray(), $rules);  // バリデーションチェック

        // 判定
        $this->assertTrue($validator->fails());             // 失敗ならTrueを返す
    }

    /**-------------------------------------------------------------------------------------------------
     * ジャンル更新（update）バリデーションチェック
     *------------------------------------------------------------------------------------------------*/
    /** 正常値を渡す **/
    public function test_ジャンル更新_バリデーションチェック_正常系()
    {
        // 準備
        $this->validData = Genre::factory()->make();        // テスト用のジャンルレコードを1件生成

        $rules = (new UpdateGenreRequest)->rules();         // バリデーションルールを取得する

        // 実行
        $validator = Validator::make($this->validData->toArray(), $rules);  // バリデーションチェック

        // 判定
        $this->assertTrue($validator->passes());            // 成功ならTrueを返す
    }

    /**
     * ジャンル名（name）関係
     */
    /** ジャンル名が無い **/
    public function test_ジャンル更新_ジャンル名が無い()
    {
        // 準備
        $this->validData = Genre::factory()->make();        // テスト用のジャンルレコードを1件生成

        $rules = (new UpdateGenreRequest)->rules();         // バリデーションルールを取得する

        $this->validData['name'] = '';                      // ジャンル名が空文字列

        // 実行
        $validator = Validator::make($this->validData->toArray(), $rules);  // バリデーションチェック

        // 判定
        $this->assertTrue($validator->fails());             // 失敗ならTrueを返す
    }

    /** ジャンル名が長すぎる **/
    public function test_ジャンル更新_ジャンル名が長すぎる()
    {
        // 準備
        $this->validData = Genre::factory()->make();        // テスト用のジャンルレコードを1件生成

        $rules = (new UpdateGenreRequest)->rules();         // バリデーションルールを取得する

        $this->validData['name'] = str_repeat('A', 51);     // ジャンル名が51文字の文字列

        // 実行
        $validator = Validator::make($this->validData->toArray(), $rules);  // バリデーションチェック

        // 判定
        $this->assertTrue($validator->fails());             // 失敗ならTrueを返す
    }

    /** ジャンル名がユニークでない **/
    public function test_ジャンル更新_ジャンル名がユニークでない()
    {
        // 準備
        $this->validData = Genre::factory()->create();      // テスト用のジャンルレコードを1件生成

        $rules = (new UpdateGenreRequest)->rules();         // バリデーションルールを取得する

        $genre = Genre::factory()->create();                // 2件目のジャンルを作成

        $genre['name'] = $this->validData['name'];          // 1件目と同じジャンル名にする

        // 実行
        $validator = Validator::make($genre->toArray(), $rules);  // バリデーションチェック

        // 判定
        $this->assertTrue($validator->fails());             // 失敗ならTrueを返す
    }
}
