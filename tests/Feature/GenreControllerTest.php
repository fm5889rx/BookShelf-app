<?php

namespace Tests\Feature;

use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

/**
 * ジャンルCRUDのテスト
 */
class GenreControllerTest extends TestCase
{
    use RefreshDatabase;                                        // データベースをリセットするトレイト

    public function test_ユーザーはジャンル一覧を取得できる()
    {
        // 準備
        $user = User::factory()->create();                      // テスト用にユーザーを1件作成

        Genre::factory()->count(3)->create();                   // テスト用にジャンルを3件作成

        // 実行
        $response = $this->actingAs($user)->get(route('genres.index'));  // 登録ジャンルを取得して一覧表示

        // 検証
        $response->assertStatus(200);                           // ステータス200を期待

        $response->assertViewIs('genres.index');                // ジャンル一覧画面が表示されることを確認

        $response->assertViewHas('genres');                     // 一覧ビューにテーブルが渡されているかを確認

    }

    public function test_ユーザーはジャンル詳細を取得できる()
    {
        //準備
        $user = User::factory()->create();                      // テスト用にユーザーを1件作成

        $genre = Genre::factory()->create();                    // テスト用にジャンルを1件作成

        $book = $genre->books()->get();                         // テスト用にジャンルに紐づく書籍情報を取得

        // 実行
        $response = $this->actingAs($user)->get(route('genres.show', $genre, $book));  // 登録ジャンルの詳細を取得

        // 検証
        $response->assertStatus(200);                           // ステータス200を期待

        $response->assertViewIs('genres.show');                 // ジャンル詳細画面が表示されることを確認

        $response->assertViewHas('genre');                      // 詳細ビューにジャンルデータが渡されているかを確認

        $response->assertViewHas('books');                      // 詳細ビューに書籍情報が渡されているかを確認
    }

    public function test_ユーザーはジャンル作成画面を表示できる()
    {
        // 準備
        $user = User::factory()->create();                      // テスト用にユーザーを1件作成

        // 実行
        $response = $this->actingAs($user)->get(route('genres.create'));  // ジャンル作成画面を表示

        // 検証
        $response->assertStatus(200);                           // ステータス200を期待

        $response->assertViewIs('genres.create');               // ジャンル作成画面が表示されることを確認
    }

    public function test_ユーザーはジャンルを作成できる()
    {
        // 準備
        $user = User::factory()->create();                      // テスト用にユーザーを1件作成

        // 実行
        $response = $this->actingAs($user)                      // ジャンル新規作成
            ->post(route('genres.store', [
                'name' => 'テストジャンル',
            ]));

        // 検証
        $response->assertRedirect(route('genres.index'));       // ジャンル一覧画面にリダイレクトされることを確認

        $this->assertDatabaseHas('genres', [                    // データベースにレコードが書き込まれているか確認
            'name' => 'テストジャンル',
        ]);
    }

    public function test_ユーザーはジャンル編集画面を表示できる()
    {
        $user = User::factory()->create();                      // テスト用にユーザーを1件作成

        $genre = Genre::factory()->create();                    // テスト用にジャンルを1件作成

        // 実行
        $response = $this->actingAs($user)                      // ジャンル作成画面を表示
            ->get(route('genres.edit', $genre->id));

        // 検証
        $response->assertStatus(200);                           // ステータス200を期待

        $response->assertViewIs('genres.edit');                 // ジャンル作成画面が表示されることを確認
    }

    public function test_ユーザーはジャンルを更新できる()
    {
        // 準備
        $user = User::factory()->create();                      // テスト用にユーザーを1件作成

        $genre = Genre::factory()->create();                    // テスト用にジャンルを1件作成

        // 実行
        $response = $this->actingAs($user)                      // ジャンル内容の更新
            ->put(route('genres.update', $genre->id), [
                'name' => 'テストジャンル',
            ]);

        // 検証
        $response->assertRedirect(route('genres.show', $genre->id));  // ジャンル詳細画面にリダイレクトされることを確認

        $this->assertDatabaseHas('genres', [                    // データベースのレコードが更新されているか確認
            'id' => $genre->id,
            'name' => 'テストジャンル',
        ]);
    }

    public function test_ユーザーはジャンルを削除できる()
    {
        // 準備
        $user = User::factory()->create();                      // テスト用にユーザーを1件作成

        $genre = Genre::factory()->create();                    // テスト用にジャンルを1件作成

        // 実行
        $response = $this->actingAs($user)                      // ジャンル内容の更新
            ->delete(route('genres.destroy', $genre->id));

        // 検証
        $response->assertRedirect(route('genres.index'));       // ジャンル一蘭画面にリダイレクトされることを確認

        $this->assertDatabaseMissing('genres', [                // データベースにレコードが書き込まれているか確認
            'id' => $genre->id,
        ]);
    }
}
