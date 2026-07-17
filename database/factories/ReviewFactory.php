<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    /**
     * レビューのモデルファクトリの定義
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),                       // ランダムなユーザーIDを生成
            'rating' => $this->faker->numberBetween(1, 5),     // ランダムな評価値を生成（1〜5）
            'comment' => $this->faker->text(255),               // ランダムな説明を生成
            'book_id' => Book::factory(),                       // ランダムな書籍IDを生成
        ];
    }
}
