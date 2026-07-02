<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGenreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
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
                Rule::unique('genres', 'name'),
            ]
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
            'name.max' => 'ジャンル名は50文字以内で入力して下さい',
            'name.unique' => 'このジャンル名は既に存在します',
        ];
    }
}
