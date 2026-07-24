<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Advanced:
 * 読書計画新規作成用のリクエスト
 */
class StoreReadingPlanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 入力値に対するバリデーションルールの定義
     */
    public function rules(): array
    {
        $rules = [
            'book_id' => [
                'required',
                'integer',
                'exists:books,id',
            ],
            'target_date' => [
                'required',
                'date',
                'date_format:Y-m-d',
            ],
        ];

        return $rules;
    }

    /**
     * バリデーションメッセージの定義（日本語）
     */
    public function messages(): array
    {
        return [
            'book_id.required' => '書籍指定は必須です',
            'book_id.integer' => '書籍指定IDは数値で指定してください',
            'book_id.exists' => '指定された書籍は存在しません',
            'target_date.required' => '期日は必須です',
            'target_date.date' => '期日は正しい日付形式で入力してください',
            'target_date.date_format' => '期日の書式はYYYY/MM/DDで入力してください',
        ];
    }
}
