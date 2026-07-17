<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGenreRequest extends FormRequest
{
    /**
     * バリデーションルールの定義
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * バリデーションルールの定義
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('genres', 'name')                      // 自分自身を除いて一意性をチェック
                    ->ignore($this->route('genre')),
            ],
        ];
    }

    /**
     * バリデーションエラーメッセージの定義(日本語)
     */
    public function messages(): array
    {
        return [
            'name.required' => 'ジャンル名は必須です',
            'name.string' => 'ジャンル名は文字列で入力して下さい',
            'name.max' => 'ジャンル名は255文字以内で入力して下さい',
            'name.unique' => 'このジャンル名は既に使われています',
        ];
    }
}
