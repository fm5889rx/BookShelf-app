<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReviewRequest extends FormRequest
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
     */
    public function rules(): array
    {
        $rules = [                                          // PUT用バリデーションルール
            'rating'  => 'required|integer|in:1,2,3,4,5',
            'comment' => 'required|string|max:255',
 //           'book_id' => 'sometimes|exists:books,id',
        ];

        return $rules;                                      // バリデーションルールを返す
    }

    /**
     * バリデーションエラーメッセージのカスタマイズ(日本語)
     */
    public function messages(): array
    {
        return [
            'rating.required'  => '評価値を選択してください',
            'comment.required' => 'コメントは必須です',
            'comment.string'   => 'コメントは文字列で入力してください',
            'comment.max'      => 'コメントは255文字以下で入力してください',
//            'book_id.required' => '書籍情報は必須です',
//            'book_id.exists'   => '指定された書籍は存在しません',
        ];
    }
}
