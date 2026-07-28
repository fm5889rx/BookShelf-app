<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Advanced:
 * マイ読書レポートのテスト
 */
class ReportControllerTest extends TestCase
{
    use RefreshDatabase;                                    // データベースをリフレッシュするトレイト

    /**
     * 読書レポート画面の表示
     */
    public function test_マイ読書レポート画面が表示できる(): void
    {
        // 準備
        $user = User::factory()->create();                  // テスト用にユーザデータを作成

        // 実行
        $response = $this->actingAs($user)                  // ログインしてマイ読書レポート画面を表示
            ->get(route('reports.index'));

        // 検証
        $response->assertStatus(200);                       // HTTPステータス200を期待（成功）

        $response->assertViewIs('reports.index');           // マイ読書レポート画面が表示されているか確認
    }

    /**
     * 未ログイン時のテスト
     */
    public function test_未ログインではマイ読書レポート画面が表示できない()
    {
        // 準備
        $user = User::factory()->create();                  // テスト用にユーザーデータを1件生成

        // 実行
        $response = $this->get(route('reports.index'));     // ログインせずにマイ読書レポート画面を表示

        // 検証
        $response->assertStatus(302);                       // ステータス302を期待（リダイレクト）

        $response->assertRedirect(route('login'));          // ログイン画面が表示されることを確認
    }
}
