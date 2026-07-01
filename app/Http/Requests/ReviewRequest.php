<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReviewRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 入力データに対するバリデーションルール
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'evaluation_value' => 'required|integer|in:1,2,3,4,5',
            'comment'          => 'required|string|max:255',
            'book_id'          => 'required|exists:books,id',
        ];
    }

    public function massages(): array
    {
        return [
            'evaluation_value.required' => '評価値を選択してください',
            'comment.equired'           => 'コメントは必須です',
            'comment.string'            => 'コメントは文字列で入力してください',
            'comment.max'               => 'コメントは255文字以下で入力してください',
            'book_id.required'          => '書籍情報は必須です',
            'book_id.exists'            => '指定された書籍は存在しません',
        ];
    }

}
