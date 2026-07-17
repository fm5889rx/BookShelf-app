<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 登録リクエストに対するバリデーションルールの定義
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ];
    }

    /**
     * バリデーションエラーメッセージのカスタマイズ
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => '名前は必須です',
            'name.string' => '名前は文字列で入力して下さい',
            'name.max' => '名前は255文字以内で入力して下さい',
            'email.required' => 'メールアドレスは必須です',
            'email.string' => 'メールアドレスは文字列で入力して下さい',
            'email.email' => 'メールアドレスの形式が正しくありません',
            'email.max' => 'メールアドレスは255文字以内で入力して下さい',
            'email.unique' => 'このメールアドレスは既に使用されています',
            'password.required' => 'パスワードは必須です',
            'password.string' => 'パスワードは文字列で入力して下さい',
            'password.min' => 'パスワードは8文字以上で入力して下さい',
            'password.confirmed' => 'パスワードの確認が一致しません',
        ];
    }
}
