<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Advanced:
 * 読書計画更新用のリクエスト
 */
class UpdateReadingPlanRequest extends FormRequest
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
            'target_date.required' => '期日は必須です',
            'target_date.date' => '期日は正しい日付形式で入力してください',
            'target_date.date_format' => '期日の書式はYYYY/MM/DDで入力してください',
        ];
    }
}
