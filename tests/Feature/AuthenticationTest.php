<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * 認証関係のテスト
 */
class AuthenticationTest extends TestCase
{
    use RefreshDatabase;                // データベースをリセットするトレイル

    /**-------------------------------------------------------------------------
     * ログイン関係
     *------------------------------------------------------------------------*/
    public function test_ログイン画面を表示できる()
    {
        // 実行
        $response = $this->get(route('login'));             // ログイン画面を表示

        // 検証
        $response->assertStatus(200);                       // HTTPステータス200を期待（正常終了）
    }

    public function test_正しい認証情報でログインできる()
    {
        // 準備
        $user = User::factory()->create([
            'password' => bcrypt('password123'),            // パスワードを指定してユーザ作成
        ]);

        // 実行
        $response = $this->post(route('login'), [           // 認証情報を渡してログイン
            'email' => $user->email,
            'password' => 'password123',
        ]);

        // 検証
        $response->assertStatus(302);                       // HTTPステータス302を期待（リダイレクト）

        $response->assertRedirect(route('books.index'));    // 書籍一覧画面に遷移することを期待

        $this->assertAuthenticatedAs($user);                // 認証成功を期待
    }

    public function test_存在しないメールアドレスではログインできない()
    {
        // 実行
        $response = $this->post(route('login'), [           // 存在しない認証情報でログイン
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
        ]);

        // 検証
        $response->assertStatus(302);                       // HTTPステータス302を期待（リダイレクト）

        $response->assertRedirect(route('login'));          // ログイン画面に戻ることを期待

        $response->assertSessionHasErrors('email');         // メールアドレスエラーが出ることを期待

        $this->assertGuest();                               // 認証されていない状態であることを期待
    }

    public function test_メールアドレスが空だとログインのバリデーションエラーになる()
    {
        // 実行
        $response = $this->post(route('login'), [           // 空のメールアドレスを渡してログイン
            'email' => '',
            'password' => 'password123',
        ]);

        // 検証
        $response->assertStatus(302);                       // HTTPステータス302を期待（リダイレクト）

        $response->assertRedirect('/');                     // 前の画面に戻ることを期待

        $response->assertSessionHasErrors('email');         // メールアドレスエラーが出ることを期待

        $this->assertGuest();                               // 認証されていない状態であることを期待
    }

    public function test_長すぎるメールアドレスだとログインのバリデーションエラーになる()
    {
        // 実行
        $response = $this->post(route('login'), [           // 255文字以上のメールアドレスを渡してログイン
            'email' => str_repeat('A', 255) . '@example.com',
            'password' => 'password123',
        ]);

        // 検証
        $response->assertStatus(302);                       // HTTPステータス302を期待（リダイレクト）

        $response->assertRedirect('/');                     // 前の画面に戻ることを期待

        $response->assertSessionHasErrors('email');         // メールアドレスエラーが出ることを期待

        $this->assertGuest();                               // 認証されていない状態であることを期待
    }

    public function test_間違ったパスワードではログインできない()
    {
        // 準備
        $user = User::factory()->create([
            'password' => bcrypt('password123'),            // パスワードを指定してユーザ作成
        ]);

        // 実行
        $response = $this->post(route('login'), [           // 誤った認証情報を渡してログイン
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        // 検証
        $response->assertStatus(302);                       // HTTPステータス302を期待（リダイレクト）

        $response->assertRedirect(route('login'));          // ログイン画面に戻ることを期待

//        $response->assertSessionHasErrors('password');      // パスワードエラーが出ることを期待

        $this->assertGuest();                               // 認証されていない状態であることを期待
    }

    public function test_パスワードが空だとログインのバリデーションエラーになる()
    {
        // 準備
        $user = User::factory()->create();                  // テストユーザを作成

        // 実行
        $response = $this->post(route('login'), [           // パスワードを空にしてログイン
            'email' => $user->email,
            'password' => '',
        ]);

        // 検証
        $response->assertStatus(302);                       // HTTPステータス302を期待（リダイレクト）

        $response->assertRedirect('/');                     // 前の画面に戻ることを期待

        $response->assertSessionHasErrors('password');      // パスワードエラーが出ることを期待

        $this->assertGuest();                               // 認証されていない状態であることを期待
    }

    public function test_短いパスワードだとログインのバリデーションエラーになる()
    {
        // 準備
        $user = User::factory()->create();                  // テストユーザを作成

        // 実行
        $response = $this->post(route('login'), [           // パスワードを7文字にしてログイン
            'email' => $user->email,
            'password' => 'passwrd',
        ]);

        // 検証
        $response->assertStatus(302);                       // HTTPステータス302を期待（リダイレクト）

        $response->assertRedirect('/');                     // 前の画面に戻ることを期待

        $response->assertSessionHasErrors('password');      // パスワードエラーが出ることを期待

        $this->assertGuest();                               // 認証されていない状態であることを期待
    }

    /**-------------------------------------------------------------------------
     * ログアウト関係
     *------------------------------------------------------------------------*/
    public function test_正常にログアウトできる()
    {
        // 準備
        $user = User::factory()->create();                  // テスト用にユーザを作成

        // 実行
        $response = $this->actingAs($user)                  // ログイン状態でログアウト実行
            ->post(route('logout'));

        // 検証
        $response->assertStatus(302);                       // HTTPステータス302を期待（リダイレクト）

        $response->assertRedirect(route('login'));          // ログイン画面に遷移すること期待

        $this->assertGuest();                               // 認証が解除されたことを確認

        $response->assertSessionMissing('login_successful');  // セッションが破棄されていることを確認
    }

    public function test_未ログイン状態ではログアウトできない()
    {
        // 準備
        $user = User::factory()->create();                  // テスト用にユーザを作成

        // 実行
        $response = $this->post(route('logout'));           // 未ログイン状態でログアウト実行

        // 検証
        $response->assertStatus(302);                       // HTTPステータス302を期待（リダイレクト）

        $response->assertRedirect(route('login'));          // ログイン画面に遷移すること期待

        $this->assertGuest();                               // 認証されていないことを確認
    }

    /**-------------------------------------------------------------------------
     * 会員登録関係
     *------------------------------------------------------------------------*/
    public function test_会員登録画面が表示される()
    {
        // 実行
        $response = $this->get(route('register'));          // 会員登録画面を表示

        // 検証
        $response->assertStatus(200);                       // HTTPステータス200を期待（正常終了）
    }

    public function test_正しい登録情報でログインできる()
    {
        // 実行
        $response = $this->post(route('register'), [        // 会員登録処理を実行
            'name' => 'テストユーザー',
            'email' => 'user@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',       // パスワード確認も含める
        ]);

        // 検証
        $response->assertStatus(302);                       // HTTPステータス302を期待（リダイレクト）

        $response->assertRedirect(route('books.index'));    // 書籍一覧画面に遷移することを期待

        $this->assertDatabaseHas('users', [                 // 新規ユーザーが登録されているかを確認
            'name' => 'テストユーザー',
            'email' => 'user@example.com',
        ]);

        $user = User::where('email', 'user@example.com')->first();  // 登録ユーザー情報を取得

        $this->assertTrue(Hash::check('password123', $user->password)); // パスワードハッシュの一致を確認

        $this->assertAuthenticatedAs($user);                // 認証成功を期待
    }

    public function test_nameが空だと会員登録のバリデーションエラーになる()
    {
        // 実行
        $response = $this->post(route('register'), [        // 会員登録処理を実行
            'name' => '',                                   // 名前が空文字列
            'email' => 'user@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',       // パスワード確認も含める
        ]);

        // 検証
        $response->assertStatus(302);                       // HTTPステータス302を期待（リダイレクト）

        $response->assertRedirect('/');                     // 前の画面に戻ることを期待

        $response->assertSessionHasErrors('name');          // メールアドレスエラーが出ることを期待

        $this->assertGuest();                               // 認証されていない状態であることを期待
    }

    public function test_長すぎるnameだと会員登録のバリデーションエラーになる()
    {
        // 実行
        $response = $this->post(route('register'), [        // 会員登録処理を実行
            'name' => str_repeat('A', 256),                 // max:255なので256文字の文字列
            'email' => 'user@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',       // パスワード確認も含める
        ]);

        // 検証
        $response->assertStatus(302);                       // HTTPステータス302を期待（リダイレクト）

        $response->assertRedirect('/');                     // 前の画面に戻ることを期待

        $response->assertSessionHasErrors('name');          // メールアドレスエラーが出ることを期待

        $this->assertGuest();                               // 認証されていない状態であることを期待
    }

    public function test_メールアドレスが空だと会員登録のバリデーションエラーになる()
    {
        // 実行
        $response = $this->post(route('register'), [        // 会員登録処理を実行
            'name' => 'テストユーザー',
            'email' => '',                                  // メールアドレスが空文字列
            'password' => 'password123',
            'password_confirmation' => 'password123',       // パスワード確認も含める
        ]);

        // 検証
        $response->assertStatus(302);                       // HTTPステータス302を期待（リダイレクト）

        $response->assertRedirect('/');                     // 前の画面に戻ることを期待

        $response->assertSessionHasErrors('email');         // メールアドレスエラーが出ることを期待

        $this->assertGuest();                               // 認証されていない状態であることを期待
    }

    public function test_メールアドレス形式でないと会員登録のバリデーションエラーになる()
    {
        // 実行
        $response = $this->post(route('register'), [        // 会員登録処理を実行
            'name' => 'テストユーザー',
            'email' => 'wrong-mailaddress',                 // メールアドレスk形式でない文字列
            'password' => 'password123',
            'password_confirmation' => 'password123',       // パスワード確認も含める
        ]);

        // 検証
        $response->assertStatus(302);                       // HTTPステータス302を期待（リダイレクト）

        $response->assertRedirect('/');                     // 前の画面に戻ることを期待

        $response->assertSessionHasErrors('email');         // メールアドレスエラーが出ることを期待

        $this->assertGuest();                               // 認証されていない状態であることを期待
    }

    public function test_長すぎるメールアドレスだと会員登録のバリデーションエラーになる()
    {
        // 実行
        $response = $this->post(route('register'), [        // 会員登録処理を実行
            'name' => 'テストユーザー',
            'email' => str_repeat('A', 256) . '@example.com',  // 256文字以上のメールアドレス
            'password' => 'password123',
            'password_confirmation' => 'password123',       // パスワード確認も含める
        ]);

        // 検証
        $response->assertStatus(302);                       // HTTPステータス302を期待（リダイレクト）

        $response->assertRedirect('/');                     // 前の画面に戻ることを期待

        $response->assertSessionHasErrors('email');         // メールアドレスエラーが出ることを期待

        $this->assertGuest();                               // 認証されていない状態であることを期待
    }

    public function test_パスワードが空だと会員登録のバリデーションエラーになる()
    {
        // 準備
        $user = User::factory()->create();                  // テストユーザを作成

        // 実行
        $response = $this->post(route('register'), [        // 会員登録処理を実行
            'name' => 'テストユーザー',
            'email' => 'user@example.com',
            'password' => '',                               // パスワードが空文字列
            'password_confirmation' => '',                  // パスワード確認も含める
        ]);

        // 検証
        $response->assertStatus(302);                       // HTTPステータス302を期待（リダイレクト）

        $response->assertRedirect('/');                     // 前の画面に戻ることを期待

        $response->assertSessionHasErrors('password');      // パスワードエラーが出ることを期待

        $this->assertGuest();                               // 認証されていない状態であることを期待
    }

    public function test_短いパスワードだと会員登録のバリデーションエラーになる()
    {
        // 準備
        $user = User::factory()->create();                  // テストユーザを作成

        // 実行
        $response = $this->post(route('register'), [        // 会員登録処理を実行
            'name' => 'テストユーザー',
            'email' => 'user@example.com',
            'password' => 'passwor',                        // min:8なので7文字の文字列
            'password_confirmation' => 'passwor',           // パスワード確認も含める
        ]);

        // 検証
        $response->assertStatus(302);                       // HTTPステータス302を期待（リダイレクト）

        $response->assertRedirect('/');                     // 前の画面に戻ることを期待

        $response->assertSessionHasErrors('password');      // パスワードエラーが出ることを期待

        $this->assertGuest();                               // 認証されていない状態であることを期待
    }

    public function test_パスワードが一致しないと会員登録のバリデーションエラーになる()
    {
        // 準備
        $user = User::factory()->create();                  // テストユーザを作成

        // 実行
        $response = $this->post(route('register'), [        // 会員登録処理を実行
            'name' => 'テストユーザー',
            'email' => 'user@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password',          // パスワード確認が違う文字列
        ]);

        // 検証
        $response->assertStatus(302);                       // HTTPステータス302を期待（リダイレクト）

        $response->assertRedirect('/');                     // 前の画面に戻ることを期待

        $response->assertSessionHasErrors('password');      // パスワードエラーが出ることを期待

        $this->assertGuest();                               // 認証されていない状態であることを期待
    }
}
