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

    public function create(User $user): bool
    {
        // 登録者のみ新規作成を許可
        return $user->id;
    }

    public function update(User $user, Book $book): bool
    {
        // 登録者のみ更新を許可
        return $user->id === $book->user_id;
    }

    public function delete(User $user, Book $book): bool
    {
        // 登録者のみ削除を許可
        return $user->id === $book->user_id;
    }
}
