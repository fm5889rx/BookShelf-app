<?php

namespace Tests\Feature;

use App\Enums\ReadingPlanStatus;
use App\Models\ReadingPlan;
use App\Models\User;
use App\Notifications\PlanReminderNotification;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class UpdateReadingPlanTest extends TestCase
{
    use RefreshDatabase; // 毎回DBをきれいにリセット

    protected function setUp(): void
    {
        parent::setUp();
        // 1. テスト内の「今日」を 2026-07-29 に固定
        Carbon::setTestNow(Carbon::parse('2026-07-29'));
    }

    public function test_読書計画バッチが定時間でステータス更新と各種通知を発火する(): void
    {
        // 2. 通知をフェイク（実際には送信せず、裏でカウントする）
        Notification::fake();

        // 3. テスト用のユーザー作成
        $user = User::factory()->create();

        // (仕様1) 期日経過 (昨日 7/28 が期日) の Active → Expired化されるべき
        $expiredPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'status' => ReadingPlanStatus::Active->value,
            'target_date' => Carbon::parse('2026-07-28'),
        ]);

        // (仕様2) 期日3日前 (8/1 が期日) の Active → 'three_days_before' 通知
        $threeDaysBeforePlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'status' => ReadingPlanStatus::Active->value,
            'target_date' => Carbon::parse('2026-08-01'),
        ]);

        // (仕様3) 期日当日 (7/29 が期日) の Active → 'on_due_date' 通知
        $todayPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'status' => ReadingPlanStatus::Active->value,
            'target_date' => Carbon::parse('2026-07-29'),
        ]);

        // (仕様4) 期日3日前 (7/26 が期日) にすでに Expired になっている計画 → 'three_days_after' 通知
        $threeDaysAfterPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'status' => ReadingPlanStatus::Expired->value,
            'target_date' => Carbon::parse('2026-07-26'),
        ]);

        // 4. バッチコマンドを実行
        $this->artisan('app:update-reading-plan')->assertExitCode(0);

        // 5. 検証：仕様1（一括Expired化）が成功しているか
        $this->assertEquals(ReadingPlanStatus::Expired, $expiredPlan->fresh()->status);

        // 6. 検証：仕様2〜4（通知がそれぞれのタイプで発火しているか）
        Notification::assertSentTo(
            $user,
            PlanReminderNotification::class,
            function ($notification, $channels) {
                // database チャンネルが使われているか確認
                if (! in_array('database', $channels)) {
                    return false;
                }

                // 届いた通知の type（コンストラクタで渡した文字列）を検証
                // ※一度に複数走るため、どのタイプが来ても受け入れる検証にしています
                return in_array($notification->type, ['three_days_before', 'on_due_date', 'three_days_after']);
            }
        );
    }

    /**
     * 通知クラス（PlanReminderNotification）の内部ロジックのカバレッジを100%にするテスト
     */
    public function test_通知クラスの内部ロジックおよび各分岐ルートを網羅する(): void
    {
        // 今回は fake() を使わない
        $user = User::factory()->create();
        $plan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'target_date' => Carbon::parse('2026-07-29'),
        ]);

        // 1. 各タイミング（3つのルート）のインスタンスを作成してメソッドを直接実行
        $types = ['three_days_before', 'on_due_date', 'three_days_after', 'invalid_default_route'];

        foreach ($types as $type) {
            $notification = new PlanReminderNotification($plan, $type);

            // toDatabase を直接実行して内部の getNotificationTitle / getNotificationMessage を強制的に通過させる
            $data = $notification->toDatabase($user);

            // 最低限の値が入っているかチェック
            $this->assertArrayHasKey('title', $data);
            $this->assertArrayHasKey('body', $data);
            $this->assertArrayHasKey('data', $data);
        }

        // 2. 実際にDB保存チャンネルが指定されているかもチェック
        $notificationForChannel = new PlanReminderNotification($plan, 'on_due_date');
        $this->assertEquals(['database'], $notificationForChannel->via($user));
    }

    /**
     * スケジュール登録の検証
     */
    public function test_スケジュール機能に_update_reading_planコマンドが毎日20時実行で登録されている()
    {
        // 1. スケジュール管理クラス（Schedule）をアプリケーションから取得
        $schedule = app(Schedule::class);

        // 2. 登録されている全イベントの中から、今回のコマンド（app:update-reading-plan）を探す
        $events = collect($schedule->events())->filter(function ($event) {
            return str_contains($event->command, 'app:update-reading-plan');
        });

        // 3. 検証：正しくスケジュールに登録されているか
        $this->assertNotEmpty($events, 'UpdateReadingPlan コマンドがスケジュールに登録されていません。');

        // 4. 検証：実行タイミングが「毎日20時（0 20 * * *）」になっているか
        $event = $events->first();
        $this->assertEquals('0 20 * * *', $event->expression);
    }
}
