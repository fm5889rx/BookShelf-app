<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Book>
 */
class BookFactory extends Factory
{
    /**
     * 書籍情報のモデルファクトリの定義
     */
    public function definition(): array
    {
        return [
            'user_id'        => User::factory(),                    // ユーザーIDをランダムに生成
            'title'          => $this->faker->sentence(3),          // ランダムなタイトルを生成
            'author'         => $this->faker->name(),               // ランダムな著者名を生成
            'published_date' => $this->faker->date(),               // ランダムな出版日を生成
            'isbn'           => $this->faker->isbn13(),             // ランダムなISBNを生成
            'description'    => $this->faker->text(255),            // ランダムな説明文を生成
            'image_url'      => $this->faker->imageUrl(),           // ランダムな画像URLを生成
            'created_at'     => now(),                              // 現在の日時を設定
            'updated_at'     => now(),                              // 現在の日時を設定
        ];
    }
}
