<?php

namespace Database\Factories;

use App\Models\Notification;
use App\Models\ReadingPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    public function definition(): array
    {
        $timings = ['three_days_before',                      // Bladeが待っている3つのタイミングを配列化
            'on_due_date',
            'three_days_after'];

        $selectedTiming = $this->faker->randomElement($timings);    // ランダムで1つ選ぶ設定

        // まだ通知（notifications）に紐付いていないユニークな読書計画のIDを1件取得する
        // 該当する計画がなければ、その場で新しく1件作成してユニーク性を100%保証する
        $uniquePlanId = ReadingPlan::whereDoesntHave('notifications')->inRandomOrder()->first()?->id
            ?? ReadingPlan::factory()->create()->id;

        //  紐付いた読書計画から、書籍のタイトルとユーザーIDを事前に取得しておく
        $plan = ReadingPlan::with('book')->find($uniquePlanId);

        $bookTitle = $plan && $plan->book ? $plan->book->title : '登録書籍';

        $userId = $plan ? $plan->user_id : 1;

        // 動的なタイトルと本文の文章を定義
        $dynamicTitle = "【リマインダー】『{$bookTitle}』の読書計画について";

        $dynamicBody = match ($selectedTiming) {
            'three_days_before' => '計画の期日まであと3日です',
            'on_due_date' => '本日が計画の期日です。読了したらステータスを「読了」に更新してください',
            'three_days_after' => '計画の期限が過ぎてから3日が経過しました。新しい目標を設定して再開してください',
            default => '読書計画の進捗を確認してください',
        };

        return [                                                    // レコードに保存する値を生成

            'reading_plan_id' => $uniquePlanId,                     // 読書計画IDをセット

            'timing' => $selectedTiming,                            // 物理カラムの timing を追加

            'title' => $dynamicTitle,                               // 物理カラムに作ったタイトルを保存

            'body' => $dynamicBody,                                 // 物理カラムに作ったbodyを保存

            // Blade用の data カラム（JSON形式）
            'data' => [

                'reading_plan_id' => $uniquePlanId,                 // 読書計画IDをセット

                'timing' => $selectedTiming,                        // 物理カラムの timing を追加

                'title' => $dynamicTitle,                           // 物理カラムに作ったタイトルを保存

                'body' => $dynamicBody,                             // 物理カラムに作ったbodyを保存

            ],

            // 読書計画に紐づいている user_id と完全に連動させる
            'notifiable_id' => $userId,                             // 生成したユーザーIDをセット

            'notifiable_type' => 'App\Models\User',                 // Userモデル固定

            'read_at' => null,                                      // 初期値は未読状態
        ];
    }
}
