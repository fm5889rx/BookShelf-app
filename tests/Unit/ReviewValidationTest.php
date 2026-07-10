<?php

namespace Tests\Unit;

use App\Models\Review;
use App\Http\Requests\StoreReviewRequest;
use App\Http\Requests\UpdateReviewRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * レビューのバリデーションチェック単体テスト
 */
class ReviewValidationTest extends TestCase
{
    use RefreshDatabase;                                    // データベースのリフレッシュを行うトレイトを使用

    protected $validData;                                   // テストで使用するレビュー情報（正常値）

    /**-------------------------------------------------------------------------------------------------
     * レビュー新規作成（store）バリデーションチェック
     *------------------------------------------------------------------------------------------------*/
    /** 正常値を渡す **/
    public function test_レビュー新規作成_バリデーションチェック_正常系()
    {
        // 準備
        $this->validData = Review::factory()->make();       // テスト用のレビューレコードを1件生成

        $rules = (new StoreReviewRequest)->rules();         // バリデーションルールを取得する

        // 実行
        $validator = Validator::make($this->validData->toArray(), $rules);  // バリデーションチェック

        // 判定
        $this->assertTrue($validator->passes());            // 成功ならTrueを返す
    }

    /**
     * 評価点（rating）関係
     */
    /** 評価点が範囲外 **/
    public function test_レビュー新規作成_評価点が範囲外_0()
    {
        // 準備
        $this->validData = Review::factory()->make();       // テスト用のレビューレコードを1件作成

        $rules = (new StoreReviewRequest)->rules();         // バリデーションルールを取得する

        $this->validData['rating'] = 0;                     // 評価点が０（正常値は１〜５）

        // 実行
        $validator = Validator::make($this->validData->toArray(), $rules);  // バリデーションチェック

        // 判定
        $this->assertTrue($validator->fails());             // 失敗ならTrueを返す
    }

    /** 評価点が範囲外 **/
    public function test_レビュー新規作成_評価点が範囲外_6()
    {
        // 準備
        $this->validData = Review::factory()->make();       // テスト用のレビューレコードを1件作成

        $rules = (new StoreReviewRequest)->rules();         // バリデーションルールを取得する

        $this->validData['rating'] = 6;                     // 評価点が６（正常値は１〜５）

        // 実行
        $validator = Validator::make($this->validData->toArray(), $rules);  // バリデーションチェック

        // 判定
        $this->assertTrue($validator->fails());             // 失敗ならTrueを返す
    }

    /**
     * コメント（comment）関係
     */
    /** コメントが無い **/
    public function test_レビュー新規作成_コメントが無い()
    {
        // 準備
        $this->validData = Review::factory()->make();       // テスト用のレビューレコードを1件生成

        $rules = (new StoreReviewRequest)->rules();         // バリデーションルールを取得する

        $this->validData['comment'] = '';                   // コメントが空文字列

        // 実行
        $validator = Validator::make($this->validData->toArray(), $rules);  // バリデーションチェック

        // 判定
        $this->assertTrue($validator->fails());             // 失敗ならTrueを返す
    }

    /** コメントが長すぎる **/
    public function test_レビュー新規作成_コメントが長すぎる()
    {
        // 準備
        $this->validData = Review::factory()->make();       // テスト用のジャンルレコードを1件生成

        $rules = (new StoreReviewRequest)->rules();         // バリデーションルールを取得する

        $this->validData['comment'] = str_repeat('A', 256); // コメントが256文字の文字列

        // 実行
        $validator = Validator::make($this->validData->toArray(), $rules);  // バリデーションチェック

        // 判定
        $this->assertTrue($validator->fails());             // 失敗ならTrueを返す
    }

    /**
     * 書籍ID（book_id）関係
     */
    /** 書籍IDが存在しない **/
    public function test_レビュー更新_存在しない書籍ID()
    {
        // 準備
        $this->validData = Review::factory()->make();       // テスト用のレビューレコードを1件生成

        $rules = (new StoreReviewRequest)->rules();         // バリデーションルールを取得する

        $this->validData['book_id'] = 99999;                // 存在しない書籍IDを設定

        // 実行
        $validator = Validator::make($this->validData->toArray(), $rules);  // バリデーションチェック

        // 判定
        $this->assertTrue($validator->fails());             // 失敗ならTrueを返す
    }
    /**-------------------------------------------------------------------------------------------------
     * レビュー更新（update）バリデーションチェック
     *------------------------------------------------------------------------------------------------*/
    /** 正常値を渡す **/
    public function test_レビュー更新_バリデーションチェック_正常系()
    {
        // 準備
        $this->validData = Review::factory()->make();       // テスト用のレビューレコードを1件生成

        $rules = (new UpdateReviewRequest)->rules();        // バリデーションルールを取得する

        // 実行
        $validator = Validator::make($this->validData->toArray(), $rules);  // バリデーションチェック

        // 判定
        $this->assertTrue($validator->passes());            // 成功ならTrueを返す
    }

    /**
     * 評価点（rating）関係
     */
    /** 評価点が範囲外 **/
    public function test_レビュー更新_評価点が範囲外_0()
    {
        // 準備
        $this->validData = Review::factory()->make();       // テスト用のレビューレコードを1件作成

        $rules = (new UpdateReviewRequest)->rules();         // バリデーションルールを取得する

        $this->validData['rating'] = 0;                     // 評価点が０（正常値は１〜５）

        // 実行
        $validator = Validator::make($this->validData->toArray(), $rules);  // バリデーションチェック

        // 判定
        $this->assertTrue($validator->fails());             // 失敗ならTrueを返す
    }

    /** 評価点が範囲外 **/
    public function test_レビュー更新_評価点が範囲外_6()
    {
        // 準備
        $this->validData = Review::factory()->make();       // テスト用のレビューレコードを1件作成

        $rules = (new UpdateReviewRequest)->rules();        // バリデーションルールを取得する

        $this->validData['rating'] = 6;                     // 評価点が６（正常値は１〜５）

        // 実行
        $validator = Validator::make($this->validData->toArray(), $rules);  // バリデーションチェック

        // 判定
        $this->assertTrue($validator->fails());             // 失敗ならTrueを返す
    }

    /**
     * コメント（comment）関係
     */
    /** コメントが無い **/
    public function test_レビュー更新_コメントが無い()
    {
        // 準備
        $this->validData = Review::factory()->make();       // テスト用のレビューレコードを1件生成

        $rules = (new UpdateReviewRequest)->rules();        // バリデーションルールを取得する

        $this->validData['comment'] = '';                   // コメントが空文字列

        // 実行
        $validator = Validator::make($this->validData->toArray(), $rules);  // バリデーションチェック

        // 判定
        $this->assertTrue($validator->fails());             // 失敗ならTrueを返す
    }

    /** コメントが長すぎる **/
    public function test_レビュー更新_コメントが長すぎる()
    {
        // 準備
        $this->validData = Review::factory()->make();       // テスト用のジャンルレコードを1件生成

        $rules = (new UpdateReviewRequest)->rules();        // バリデーションルールを取得する

        $this->validData['comment'] = str_repeat('A', 256); // コメントが256文字の文字列

        // 実行
        $validator = Validator::make($this->validData->toArray(), $rules);  // バリデーションチェック

        // 判定
        $this->assertTrue($validator->fails());             // 失敗ならTrueを返す
    }
}
