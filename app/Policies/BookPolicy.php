<?php

namespace App\Policies;

use App\Models\Book;
use App\Models\User;

class BookPolicy
{
    /**
     * 書籍情報の認可ポリシー
     */
    public function __construct()
    {
        //
    }

    public function update(User $user, Book $book): bool
    {
        // 登録者のみ編集を許可
        return $user->id === $book->user_id;
    }

    public function delete(User $user, Book $book): bool
    {
        // 登録者のみ削除を許可
        return $user->id === $book->user_id;
    }
}
