<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    /**
     * レビューの認可ポリシー
     */
    public function __construct()
    {
        //
    }

    public function update(User $user, Review $review): bool
    {
        // 投稿者本人なら更新を許可
        return $user->id === $review->user_id;
    }

    public function delete(User $user, Review $review): bool
    {
        // 投稿者本人なら削除を許可
        return $user->id === $review->user_id;
    }
}
