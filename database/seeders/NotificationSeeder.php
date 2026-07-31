<?php

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::find(1);                              // ユーザーID=1のユーザー情報を取り出す

        Notification::factory()->count(5)->create([         // そのユーザー宛ての通知を5件自動で作る
            'notifiable_id' => $user->id,
            'notifiable_type' => get_class($user),
        ]);
    }
}
