<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ApiUpdateBookRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 書籍登録のバリデーションルール
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'isbn' => [
                'required', 'string','digits:13',             // isbnの一意性及び自分自身のレコードを除外
                Rule::unique('books', 'isbn')->ignore($this->route('book')),
            ],
            'published_date' => 'required|date|date_format:Y-m-d',
            'description' => 'nullable|string|max:255',
            'image_url' => 'nullable|string|url|active_url',
            'user_id' => 'required|exists:users,id',
            'genres' => 'required|array|min:1',
            'genres.*' => 'integer|exists:genres,id',
        ];
    }

    /**
     * バリデーションエラー時のメッセージ（日本語）
     */
    public function messages(): array
    {
        return [
            'title.required' => '書籍名は必須です',
            'title.string' => '書籍名は文字列で入力してください',
            'title.max' => '書籍名は255文字以下で入力してください',
            'author.required' => '著者は必須です',
            'author.string' => '著者は文字列で入力してください',
            'author.max' => '著者は255文字以下で入力してください',
            'isbn.required' => 'ISBNコードは必須です',
            'isbn.digits' => 'ISBNコードは13桁の数字で入力してください',
            'isbn.unique' => 'このISBNコードは既に使われています',
            'published_date.required' => '出版日は必須です',
            'published_at.date' => '出版日は有効な日付形式で入力してください',
            'published_date.date_format' => '出版日はYYYY/MM/DD形式で入力してください',
            'description.string' => '説明は文字列で入力してください',
            'description.max' => '説明は255文字以内で入力してください',
            'image_url.url' => '無効なURLです',
            'image_url.active_url' => '指定されたURLが見つかりません',
            'user_id.required' => 'ユーザーIDは必須です',
            'user_id.exists' => '指定されたユーザーは存在しません',
            'genres.required' => 'ジャンルを１つ以上選択してください',
            'genres.*.exists' => '指定されたジャンルは存在しません',
        ];
    }
}
