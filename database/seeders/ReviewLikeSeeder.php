<?php

namespace Database\Seeders;

use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewLikeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 全レビューに対して処理
        Review::chunk(10, function ($reviews) {
            //連想配列から順に取り出す
            foreach ($reviews as $review) {

                // 登録ユーザー数を取得
                $maxUsers = User::max('id');

                // ランダムなユーザーID数を生成
                $userCount = random_int(1, $maxUsers);

                // すでにレビュー登録user_id（$review->user_id）が入っていないように除外
                $possibleIds = range(1, $maxUsers);

                // 既存のユーザーと重複しないよう排除
                $possibleIds = array_diff($possibleIds, [$review->user_id]);

                // 配列が空の時は次のループへ
                if (!count($possibleIds)) {
                    continue;
                }

                // 取得数を調整
                $userCount = min($userCount, count($possibleIds));

                // ランダムに指定件数だけ選択
                $selectedIds = array_rand(array_flip($possibleIds), $userCount);

                // array_randが1だけの場合はスカラになるので配列に揃える
                $selectedIds = (array)$selectedIds;

                // レコード書き込み
                $review->likedByUsers()->syncWithoutDetaching($selectedIds);
            }
        });
    }
}
