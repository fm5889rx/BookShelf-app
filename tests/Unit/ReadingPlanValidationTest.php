<?php

use App\Enums\ReadingPlanStatus;
use App\Http\Requests\StoreReadingPlanRequest;
use App\Http\Requests\UpdateReadingPlanRequest;
use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * 読書計画のバリデーションチェック単体テスト
 */
class ReadingPlanValidationTest extends TestCase
{
    use RefreshDatabase;                                    // データベースのリフレッシュを行うトレイトを使用

    protected $validData;                                   // テストで使用するレビュー情報（正常値）

    /**-----------------------------------------------------
     * 読書計画新規作成（store）バリデーションチェック
     *----------------------------------------------------*/
    /** 正常値を渡す **/
    public function test_読書計画新規作成_バリデーションチェック_正常系()
    {
        // 準備
        $this->validData = ReadingPlan::factory()->make();  // テスト用のレコードを1件生成

        $rules = (new StoreReadingPlanRequest)->rules();     // バリデーションルールを取得する

        // 実行
        $validator = Validator::make($this->validData->toArray(), $rules);  // バリデーションチェック

        // 判定
        $this->assertTrue($validator->fails());            // 成功ならTrueを返す
    }

    /**
     * 書籍ID（book_id）関係
     */
    /** 書籍IDが存在しない **/
    public function test_読書計画新規作成_存在しない書籍id()
    {
        // 準備
        $this->validData = ReadingPlan::factory()->make();  // テスト用のレコードを1件生成

        $rules = (new StoreReadingPlanRequest)->rules();    // バリデーションルールを取得する

        $this->validData['book_id'] = 9999;                 // 存在しない書籍IDを設定

        // 実行
        $validator = Validator::make($this->validData->toArray(), $rules);  // バリデーションチェック

        // 判定
        $this->assertTrue($validator->fails());             // 失敗ならTrueを返す
    }

    /**
     * 期日（target_date）関係
     */
    /** 期日が無い **/
    public function test_読書計画新規作成_期日が無い()
    {
        // 準備
        $this->validData = ReadingPlan::factory()->make();  // テスト用のレコードを1件生成

        $rules = (new StoreReadingPlanRequest)->rules();    // バリデーションルールを取得する

        $this->validData['target_date'] = '';               // 出版日が空文字列

        // 実行
        $validator = Validator::make($this->validData->toArray(), $rules);  // バリデーションチェック

        // 判定
        $this->assertTrue($validator->fails());             // 失敗ならTrueを返す
    }

    /** 期日が有効な日付形式でない **/
    public function test_読書計画新規作成_期日が有効な日付形式でない()
    {
        // 準備
        $this->validData = ReadingPlan::factory()->make();  // テスト用のレコードを1件生成

        $rules = (new StoreReadingPlanRequest)->rules();    // バリデーションルールを取得する

        $this->validData['target_date'] = '31-07-2026';     // 出版日が有効な日付形式でない

        // 実行
        $validator = Validator::make($this->validData->toArray(), $rules);  // バリデーションチェック

        // 判定
        $this->assertTrue($validator->fails());             // 失敗ならTrueを返す
    }

    /**------------------------------------------------------
     * 読書計画更新（update）バリデーションチェック
     *-----------------------------------------------------*/
    /** 正常値を渡す **/
    public function test_読書計画更新_バリデーションチェック_正常系()
    {
        // 準備
        $this->validData = ReadingPlan::factory()->make();  // テスト用のレビューレコードを1件生成

        $rules = (new UpdateReadingPlanRequest)->rules();   // バリデーションルールを取得する

        // 実行
        $validator = Validator::make($this->validData->toArray(), $rules);  // バリデーションチェック

        // 判定
        $this->assertTrue($validator->fails());            // 成功ならTrueを返す
    }

    /**
     * 期日（target_date）関係
     */
    /** 期日が無い **/
    public function test_読書計画更新_期日が無い()
    {
        // 準備
        $this->validData = ReadingPlan::factory()->make();  // テスト用のレコードを1件生成

        $rules = (new UpdateReadingPlanRequest)->rules();   // バリデーションルールを取得する

        $this->validData['target_date'] = '';               // 出版日が空文字列

        // 実行
        $validator = Validator::make($this->validData->toArray(), $rules);  // バリデーションチェック

        // 判定
        $this->assertTrue($validator->fails());             // 失敗ならTrueを返す
    }

    /** 期日が有効な日付形式でない **/
    public function test_読書計画更新_期日が有効な日付形式でない()
    {
        // 準備
        $this->validData = ReadingPlan::factory()->make();  // テスト用のレコードを1件生成

        $rules = (new UpdateReadingPlanRequest)->rules();   // バリデーションルールを取得する

        $this->validData['target_date'] = '31-07-2026';     // 出版日が有効な日付形式でない

        // 実行
        $validator = Validator::make($this->validData->toArray(), $rules);  // バリデーションチェック

        // 判定
        $this->assertTrue($validator->fails());             // 失敗ならTrueを返す
    }

    /**
     * ENUMのバリデーションチェック
     **/
    public function test_有効な_enumの値はバリデーションを通過する()
    {
        Sanctum::actingAs(User::factory()->create());

        $response = $this->post(route('reading-plans.store'), [
            'title' => 'テスト書籍',
            'status' => ReadingPlanStatus::Active->value, // 有効な値を指定
        ]);

        $response->assertStatus(302);
    }

    public function test_無効な_enumの値はバリデーションで弾かれる()
    {
        Sanctum::actingAs(User::factory()->create());

        $response = $this->post(route('reading-plans.store'), [
            'title' => 'テスト書籍',
            'status' => 'invalid-status-value', // 存在しない無効な値
        ]);

        // 422エラーになり、statusに関するエラーメッセージが含まれているか
        $response->assertStatus(302);
    }
}
