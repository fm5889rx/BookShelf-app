<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Genre>
 */
class GenreFactory extends Factory
{
    /**
     * ジャンルのモデルファクトリの定義
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word(),   // ランダムで一意性のジャンル名を生成
            'created_at' => now(),                      // 現在時刻を設定
            'updated_at' => now(),                      // 現在時刻を設定
        ];
    }
}
