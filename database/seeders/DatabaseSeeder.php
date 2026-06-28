<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        /**
         * 全Seederを記述順に実行する
         */
        $this->call([
            UserSeeder::class,              // ユーザー
            GenreSeeder::class,             // ジャンル
            BookSeeder::class,              // 書籍情報
            ReviewSeeder::class,            // レビュー
            FavoriteSeeder::class,          // お気に入り
            ReviewLikeSeeder::class,        // いいね
        ]);
    }
}
