<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApiSearchBookRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'keyword' => 'nullable|string|max:255',
            'genre_id' => 'nullable|integer|exists:genres,id',
            'per_page' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'keyword.string' => 'キーワードは文字列で入力してください',
            'keyword.max' => 'キーワードは255文字以内で入力してください',
            'genre_id.integer' => 'ジャンルIDは整数で入力してください',
            'genre_id.exists' => '指定されたジャンルIDは存在しません',
            'per_page.integer' => '1ページあたりの表示件数は整数で入力してください',
            'per_page.min' => '1ページあたりの表示件数は1以上で入力してください',
            'per_page.max' => '1ページあたりの表示件数は100以下で入力してください',
            'page.integer' => 'ページ番号は整数で入力してください',
            'page.min' => 'ページ番号は1以上で入力してください',
        ];
    }
}
