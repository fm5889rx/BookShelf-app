<?php

namespace Database\Seeders;

use App\Models\User;
use Faker\Factory;
use Illuminate\Database\Seeder;

class FavoriteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $maxUser = 5;                                   // 登録ユーザー数

        $faker = Factory::create();                     // Fakerモデルインスタンスを作成

        for ($userId = 1; $userId <= $maxUser; $userId++) {  // 登録ユーザー数回繰り返す

            $user = User::findOrFail($userId);          // ユーザー情報を取得

            $bookCount = $faker->numberBetween(3, 5);   // 3〜5の数字を生成

            $bookIds = [];                              // レコード保存用の空配列を用意

            while (count($bookIds) < $bookCount) {      // 3〜5件数に到達するまで繰り返す

                $id = random_int(1, 11);                // ランダムな書籍IDを生成

                if (! in_array($id, $bookIds)) {         // 書籍IDの重複チェック

                    $bookIds[] = $id;                   // 未登録IDのみ配列に保存
                }
            }

            $user->favoriteBooks()->syncWithoutDetaching($bookIds); // レコード書き込み
        }
    }
}
