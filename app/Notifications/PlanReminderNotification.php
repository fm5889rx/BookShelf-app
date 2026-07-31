<?php

namespace App\Notifications;

use App\Models\ReadingPlan;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

class PlanReminderNotification extends Notification
{
    use Queueable;

    public ReadingPlan $plan;

    public string $type;

    public function __construct(ReadingPlan $plan, string $type)
    {
        $this->plan = $plan;
        $this->type = $type; // 'three_days_before', 'on_due_date', 'three_days_after' が入る
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        // $notification->data['timing'] で Blade から読めるように構造を最適化
        return [
            'title' => $this->getNotificationTitle(),
            'body' => $this->getNotificationMessage(),
            'timing' => $this->type, // notificationsテーブルのtimingカラム用
            'data' => [
                'reading_plan_id' => $this->plan->id,
                'timing' => $this->type,
            ],
        ];
    }

    private function getNotificationTitle(): string
    {
        return match ($this->type) {
            'three_days_before' => '【まもなく期日】読書計画の進捗はいかがですか？',
            'on_due_date' => '【本日締切】読書計画の最終日です！',
            'three_days_after' => '【再チャレンジ】もう一度読書を始めてみませんか？',
            default => '読書計画のリマインダー',
        };
    }

    private function getNotificationMessage(): string
    {
        $dueDateStr = $this->plan->target_date ? Carbon::parse($this->plan->target_date)->format('Y/m/d') : '';

        return match ($this->type) {
            'three_days_before' => "計画の期日（{$dueDateStr}）まであと3日です。無理のないペースで読み進めましょう！",
            'on_due_date' => "本日が計画の期日（{$dueDateStr}）です。読了したらステータスを「読了」に更新してくださいね。",
            'three_days_after' => '計画の期限が過ぎてから3日が経過しました。新しい目標を設定して、読書を再開してみましょう！',
            default => '読書計画の進捗を確認してください。',
        };
    }
}
