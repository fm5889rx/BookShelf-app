<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\ReadingPlan;
use App\Notifications\PlanReminderNotification;
use App\Enums\ReadingPlanStatus;

class UpdateReadingPlan extends Command
{
    protected $signature = 'app:update-reading-plan';
    protected $description = '期日経過計画のステータス変更および各種リマインダー通知の送信';

    public function handle()
    {
        $today = Carbon::today();

        // 1. 期日経過した Active（読書中）計画を一括 Expired（期限超過）化
        $expiredCount = ReadingPlan::where('status', ReadingPlanStatus::Active->value)
            ->where('target_date', '<', $today)
            ->update(['status' => ReadingPlanStatus::Expired->value]);

        $this->info("{$expiredCount} 件の計画を期限超過に変更しました。");

        // --- 各種リマインダー通知の発火（Bladeの判定文字に合わせる） ---

        // 2. 期日 3 日前の Active 計画（予告）
        $threeDaysBefore = Carbon::today()->addDays(3);
        $this->sendNotification($threeDaysBefore, ReadingPlanStatus::Active, 'three_days_before');

        // 3. 期日当日の Active 計画（最終リマインド）
        $this->sendNotification($today, ReadingPlanStatus::Active, 'on_due_date');

        // 4. 期日 3 日後の Expired 計画（再エンゲージメント）
        $threeDaysAfter = Carbon::today()->subDays(3);
        $this->sendNotification($threeDaysAfter, ReadingPlanStatus::Expired, 'three_days_after');

        $this->info('リマインダー通知の処理が完了しました。');
        return Command::SUCCESS;
    }

    private function sendNotification(Carbon $date, ReadingPlanStatus $status, string $type)
    {
        $plans = ReadingPlan::with('user')
            ->where('status', $status->value)
            ->whereDate('target_date', $date)
            ->get();

        foreach ($plans as $plan) {
            if ($plan->user) {
                $plan->user->notify(new PlanReminderNotification($plan, $type));
            }
        }
    }
}
