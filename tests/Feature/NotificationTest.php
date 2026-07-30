<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        // 1. テストユーザーを作成してログイン状態にする
        $this->user = User::factory()->create(['id' => 1]);
        $this->actingAs($this->user);
    }

    /** @test */
    public function test_通知一覧画面にタイミングやタイトルが正しく表示されること(): void
    {
        // 既存の on_due_date に加えて、残りの2パターンもデータベースに仕込む
        Notification::factory()->create([
            'notifiable_id' => $this->user->id,
            'notifiable_type' => get_class($this->user),
            'data' => [
                'timing' => 'three_days_before', // 🔍 青いカレンダーのルート
                'title' => '3日前の通知',
                'body' => '読書計画の3日前です。',
            ],
        ]);

        Notification::factory()->create([
            'notifiable_id' => $this->user->id,
            'notifiable_type' => get_class($this->user),
            'data' => [
                'timing' => 'three_days_after', // 🔍 赤い警告マークのルート
                'title' => '3日後の通知',
                'body' => '期限から3日が経過しました。',
            ],
        ]);

        // 画面にアクセスして文字が見えているか検証
        $response = $this->get('/notifications');
        $response->assertStatus(200);
        $response->assertSee('3日前の通知');
        $response->assertSee('3日後の通知');
    }

    /** @test */
    public function test_通知を既読にするとread_atに日時が入り元の画面に戻ること(): void
    {
        // 2. テスト用の未読通知を作成
        $notification = Notification::factory()->create([
            'notifiable_id' => $this->user->id,
            'notifiable_type' => get_class($this->user),
            'read_at' => null,
        ]);

        // 3. 「前の画面（一覧）」から既読ボタンを押したという状況を作る
        $previousUrl = url('/notifications');

        // 4. 既読処理（read）のURLへPOSTリクエストを送信
        $response = $this->from($previousUrl)->post("/notifications/{$notification->id}/read");

        // 5. 【検証】元の画面にリダイレクトされるか
        $response->assertRedirect($previousUrl);

        // 6. 【検証】データベースの read_at が NULL ではなくなっているか
        $this->assertDatabaseMissing('notifications', [
            'id' => $notification->id,
            'read_at' => null,
        ]);
    }

    /** @test */
    public function test_他人の通知を既読にしようとした場合は404エラーになること(): void
    {
        // 1. 自分とは「別のユーザー」を作成
        $otherUser = User::factory()->create(['id' => 999]);

        // 2. その「他人宛て」の通知を作成する
        $otherNotification = Notification::factory()->create([
            'notifiable_id' => $otherUser->id,
            'notifiable_type' => get_class($otherUser),
            'read_at' => null,
        ]);

        // 3. ログインしている「自分」の状態で、他人の通知を既読にするURLを叩く
        $response = $this->post("/notifications/{$otherNotification->id}/read");

        // 4. 【検証】コントローラーの findOrFail が作動して 404 になるか
        $response->assertStatus(404);

        // 5. 【検証】データベースのデータが「NULLのまま（既読になっていない）」であることを確認
        $this->assertDatabaseHas('notifications', [
            'id' => $otherNotification->id,
            'read_at' => null,
        ]);
    }
}
