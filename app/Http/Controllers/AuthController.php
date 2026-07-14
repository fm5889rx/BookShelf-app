<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
   /**
     * ユーザーログインの処理
     */
    public function login(LoginRequest $request)
    {
        // バリデーションはLoginRequestで行われるため、ここではリクエストが有効であることが保証されている

        // 認証の試行
        if (Auth::attempt($request->only('email', 'password'))) {
            // 認証成功
            return redirect()->route('books.index')->with('success', 'ログインに成功しました');
        }

        // 認証失敗
        return redirect(route('login'))
            ->withInput($request->only('email'))
            ->withErrors(['email' => 'メールアドレスまたはパスワードが正しくありません']);
    }

    /**
     * ログアウトの処理
     */
    public function logout(Request $request)
    {
        // セッションを破棄してログアウト
        Auth::logout();                                 // Fortifyを通してログアウト->認証情報を削除

        $request->session()->invalidate();            // セッションIDを無効化し全てのセッションデータを削除

        $request->session()->regenerateToken();         // CSRF用トークンを新しく生成

        return redirect()->route('login');              // ログイン画面に遷移
    }

    /**
     * ユーザー登録の処理
     */
    public function register(RegisterRequest $request)
    {
        // バリデーションはRegisterRequestで行われるため、ここではリクエストが有効であることが保証されている

        $user = User::create([                          // ユーザーの新規作成
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        if ($user) {                                    // ユーザーの新規登録に成功したか？

            Auth::login($user);                         // ユーザーをログインさせる

            return redirect()->route('books.index')     // 登録完了後のリダイレクト
                ->with('success', 'ユーザー登録が完了しました');

        } else {                                        // ユーザー登録失敗ならば

            return redirect(route('login'))             // エラーを返してログイン画面にリダイレクト
                ->withInput($request->only('email'))
                ->withErrors(['email' => '会員登録に失敗しました']);
        }
    }
}
