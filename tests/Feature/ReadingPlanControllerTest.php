<?php

namespace Tests\Feature;

use App\Enums\ReadingPlanStatus;
use App\Http\Middleware\Authenticate;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Advanced:
 * 読書計画CRUDテスト
 */
class ReadingPlanControllerTest extends TestCase
{
    use RefreshDatabase;                                        // データベースをリセットするトレイト

    /**
     * @test
     */
    public function test_読書計画一覧_未ログインユーザーはログイン画面にリダイレクトされる(): void
    {
        // 1. ログインせずに（actingAsを呼ばずに）読書計画一覧へアクセス
        $response = $this->withoutMiddleware(Authenticate::class)
            ->get(route('reading-plans.index'));

        // 2. コントローラー内部の「return redirect()->route('login');」の挙動を検証
        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    /**
     * @test
     */
    public function test_読書計画一覧_ログイン済みの場合は全件がページネーション付きで表示される(): void
    {
        // 1. 準備：ユーザーを作成してログイン状態にする
        $user = User::factory()->create();
        $this->actingAs($user);

        // 読書計画を11件作成（10件を超えるページネーションを検証するため）
        ReadingPlan::factory()->count(11)->create([
            'user_id' => $user->id,
            'status'  => ReadingPlanStatus::Active->value,
        ]);

        // 2. 実行：クエリパラメータなしでアクセス
        $response = $this->get(route('reading-plans.index'));

        // 3. 検証：200 OK とビューに正しい変数が渡っていること
        $response->assertStatus(200);
        $response->assertViewIs('reading-plans.index');
        $response->assertViewHas('readingPlans');
        $response->assertViewHas('currentStatus', null);

        // 10件/ページでページネーションが動いているか検証
        $viewPlans = $response->viewData('readingPlans');
        $this->assertEquals(10, $viewPlans->perPage());
    }

    /**
     * @test
     */
    public function test_読書計画一覧_ステータス指定がある場合はwhenの中を通過して絞り込まれる(): void
    {
        // 1. 準備
        $user = User::factory()->create();
        $this->actingAs($user);

        // Active（読書中）を1件、Expired（期限超過）を1件作成
        $activePlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'status'  => ReadingPlanStatus::Active->value,
        ]);
        $expiredPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'status'  => ReadingPlanStatus::Expired->value,
        ]);

        // 2. 実行：status=Active で絞り込みクエリを投げる（when() を強制通過させる）
        $response = $this->get(route('reading-plans.index', ['status' => ReadingPlanStatus::Active->value]));

        // 3. 検証
        $response->assertStatus(200);

        $viewPlans = $response->viewData('readingPlans');
        // Active の計画のみがヒットし、Expired は除外されていることをアサート
        $this->assertTrue($viewPlans->contains($activePlan->id));
        $this->assertFalse($viewPlans->contains($expiredPlan->id));

        $response->assertViewHas('currentStatus', ReadingPlanStatus::Active->value);
    }

    /**
     * @test
     */
    public function test_読書計画作成画面を表示できる(): void
    {
        // 1. 準備：画面に表示するための書籍データを何件か作成しておく
        $user = User::factory()->create();
        $this->actingAs($user);

        \App\Models\Book::factory()->count(3)->create(['user_id' => $user->id]);

        // 2. 実行：作成画面（create）にアクセス
        // ※実際のルート名（例: reading-plans.create）に合わせてください
        $response = $this->get(route('reading-plans.create'));

        // 3. 検証：200 OK と、書籍データ（books）がビューに渡っていることを確認
        $response->assertStatus(200);
        $response->assertViewIs('reading-plans.create');
        $response->assertViewHas('books');

        // ビューに渡ったデータが空でないことを確認
        $viewBooks = $response->viewData('books');
        $this->assertCount(3, $viewBooks);
    }

    public function test_ユーザーは読書計画編集画面を表示できる(): void
    {
        // 準備
        $user = User::factory()->create();                      // テスト用にユーザーを1件生成

        $book = Book::factory()->create(['user_id' => $user->id]);  // テスト用にuser_idを登録した書籍情報を生成

        $plan = ReadingPlan::factory()->create([          // テスト用にuser_idとbook_idを登録した読書計画
            'book_id' => $book->id,
            'user_id' => $user->id,
        ]);

        // 実行
        $response = $this->actingAs($user)                      // 読書計画編集画面を表示
            ->get(route('reading-plans.edit', [
                $plan->id,
            ]));

        // 検証
        $response->assertStatus(200);                           // ステータス200を期待

        $response->assertViewIs('reading-plans.edit');        // 読書計画編集画面が表示されていることを確認
    }

    public function test_ユーザーは読書計画を新規作成できる(): void
    {
        // 準備
        $user = User::factory()->create();                      // テスト用にユーザーを1件生成

        $book = Book::factory()->create([                   // テスト用にuser_idを登録した書籍情報を生成
            'user_id' => $user->id,
        ]);

        // 実行
        $response = $this->actingAs($user)                      // 読書計画新規作成を呼び出す
            ->post(route('reading-plans.store'), [
                'user_id' => $user->id,
                'book_id' => $book->id,
                'start_date' => Carbon::today()->toDateString(),
                'target_date' => Carbon::today()->addDays(3)->toDateString(),
                'status' => ReadingPlanStatus::Inactive->value,
                'completed_at' => null,
        ]);

        // 検証
        $response->assertStatus(302);                           // 前のページにリダイレクトされていることを確認

        $this->assertDatabaseHas('reading_plans', [             // データベースに保存されていることを確認
            'user_id' => $user->id,
            'book_id' => $book->id,
            'start_date' => Carbon::today()->toDateString(),
            'target_date' => Carbon::today()->addDays(3)->toDateString(),
            'status' => ReadingPlanStatus::Inactive->value,
            'completed_at' => null,
        ]);
    }

    public function test_読書計画登録_異常系_同じ本が既に登録されている場合はエラーになる(): void
    {
        // 1. 準備：ユーザーと本を作成し、すでに1件読書計画を登録しておく（重複状態を作る）
        $user = User::factory()->create();
        $this->actingAs($user);
        $book = Book::factory()->create(['user_id' => $user->id]);

        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        // 同じ book_id でもう一度ポストするデータを準備
        $postData = [
            'book_id'     => $book->id,
            'target_date' => Carbon::today()->addMonth()->format('Y-m-d'),
        ];

        // 2. 実行：POSTリクエストを送信（コード内の if ($checkRecord->isNotEmpty()) を通過させる）
        $response = $this->post(route('reading-plans.store'), $postData);

        // 3. 検証：元の画面にリダイレクトされ、入力値が保持され、エラーメッセージがあること
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['book_id' => '同じ本が既に登録されています']);
        $response->assertSessionHas('_old_input', function ($oldInput) {
            return isset($oldInput['book_id']);
        });
    }

    public function test_ユーザーは読書計画を更新できる(): void
    {
        // 準備
        $user = User::factory()->create();                      // テスト用にユーザーを1件生成

        $book = Book::factory()->create(['user_id' => $user->id]);  // テスト用にuser_idを登録した書籍情報を生成

        $plan = ReadingPlan::factory()->create([          // テスト用にuser_idとbook_idを登録した読書計画
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        // 実行
        $response = $this->actingAs($user)                      // 読書計画を更新
            ->put(route('reading-plans.update', $plan->id), [
            'target_date' => Carbon::today()->addDays(5)->toDateString(),
        ]);

        // 検証
        $response->assertRedirect(route('reading-plans.index')); // 読書計画一覧画面が表示されることを確認

        $this->assertDatabaseHas('reading_plans', [             // データベースが更新されていることを確認
            'id' => $plan->id,
            'user_id' => $user->id,
            'book_id' => $book->id,
            'start_date' => $plan->start_date->toDateString(),
            'target_date' => Carbon::today()->addDays(5)->toDateString(),
            'status' => $plan->status,
            'completed_at' => $plan->completed_at,
        ]);
    }

    public function test_ユーザーはレビューを削除できる(): void
    {
        // 準備
        $user = User::factory()->create();                      // テスト用にユーザーを1件生成

        $book = Book::factory()->create(['user_id' => $user->id]);  // テスト用にuser_idを登録した書籍情報を生成

        $plan = ReadingPlan::factory()->create([          // テスト用にuser_idとbook_idを登録した読書計画
            'book_id' => $book->id,
            'user_id' => $user->id,
        ]);

        // 実行
        $response = $this->actingAs($user)                      // 読書計画を削除
            ->delete(route('reading-plans.destroy', $plan->id));

        // 検証
        $this->assertDatabaseMissing('reading_plans', [       // データベースからレコードが削除さfれているかを確認
            'id' => $plan->id,
        ]);
    }

    public function test_読書計画を完了に更新して一覧画面を表示できること(): void
    {
        // 1. ログインが必要なミドルウェアがある場合はログイン状態にする
        $this->actingAs(User::factory()->create());

        // 2. テスト用の「未完了（Completed以外）」の読書計画データを1件作成
        $readingPlan = ReadingPlan::factory()->create([
            'status' => ReadingPlanStatus::Active, // 実際の未完了ステータス値
            'completed_at' => null,
        ]);

        // 3. テスト実行時の時間を固定する（現在日時の completed_at を正確に検証するため）
        Carbon::setTestNow(now());

        // 4. complete メソッドに対応するURLにPOSTやPUTリクエストを送る
        // ※ルーティングの設定に合わせて url や HTTPメソッド（post/put/get）を変更してください
        $response = $this->post("/reading-plans/{$readingPlan->id}/complete");

        // 5. 【検証】画面が正常に表示されたか（200 OK）
        $response->assertStatus(200);

        // 6. 【検証】指定のViewテンプレートが使われ、変数が渡されているか
        $response->assertViewIs('reading-plans.index');
        $response->assertViewHas('readingPlans');
        $response->assertViewHas('currentStatus', null); // フィルタがクリアされているか

        // 7. 【検証】データベースの値が正しく「完了」に書き換わっているか
        $this->assertDatabaseHas('reading_plans', [
            'id' => $readingPlan->id,
            'status' => ReadingPlanStatus::Completed,
            'completed_at' => now(), // 固定した現在日時と一致するか
        ]);

        // 8. 時間の固定を解除（お作法）
        Carbon::setTestNow();
    }
}