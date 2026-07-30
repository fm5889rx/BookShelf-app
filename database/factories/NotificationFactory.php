<?php

namespace Database\Factories;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    public function definition(): array
    {
        // Bladeが待っている3つのタイミングから、ランダムで1つ選ぶ設定
        $timings = ['three_days_before', 'on_due_date', 'three_days_after'];
        $selectedTiming = $this->faker->randomElement($timings);

        return [
            'data' => [
                'timing' => $selectedTiming, // とりあえず固定の時間
                'title' => 'テストの通知タイトル',
                'body' => 'テストの通知本文がここに入ります。',
            ],
            'notifiable_id' => 1, // とりあえず1番
            'notifiable_type' => 'App\Models\User', // ここは固定の文字列でOK
            'read_at' => null, // 未読状態
        ];
    }
}
