<?php

namespace App\Policies;

use App\Models\ReadingPlan;
use App\Models\User;

/**
 * Advanced:
 * 読書計画の編集／削除ポリシーの定義
 */
class ReadingPlanPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function edit(User $user, ReadingPlan $plan): bool
    {
        // 登録者のみ編集を許可
        return $user->id === $plan->user_id;
    }

    public function update(User $user, ReadingPlan $plan): bool
    {
        // 登録者のみ編集を許可
        return $user->id === $plan->user_id;
    }

    public function delete(User $user, ReadingPlan $plan): bool
    {
        // 登録者のみ削除を許可
        return $user->id === $plan->user_id;
    }
}
