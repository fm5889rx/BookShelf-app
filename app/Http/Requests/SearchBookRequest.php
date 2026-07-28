<?php

namespace App\Http\Requests;

use GuzzleHttp\Promise\FulfilledPromise;
use Illuminate\Foundation\Http\FormRequest;

class SearchBookRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 書籍一覧・検索パラメータのバリデーションルール
     */
    public function rules(): array
    {
        return [
            'keyword'  => 'nullable|string|max:255',
            'per_page' => 'nullable|integer|min:1|max:100',
            'page'     => 'nullable|integer|min:1',
        ];
    }

    /**
     * 検索パラメータのエラーメッセージ（日本語対応）
     */
    public function messages(): array
    {
        return [
            'keyword.string'  => 'タイトル又は著者は文字列で入力してください',
            'keyword.max'     => 'タイトル又は著者は255文字以下で入力してください',
            'per_page.min'    => '1ページあたりの冊数は1以上を入力してください',
            'per_page.min'    => '1ページあたりの冊数は100以下を入力してください',
            'page.min'        => 'ページ数は1以上を入力してください',
        ];
    }
}
