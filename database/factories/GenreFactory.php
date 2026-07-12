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
            // ランダムで一意性のジャンル名を生成
            'name' => $this->faker->unique()->regexify('[A-Za-z]{4,10}'),
            'created_at' => now(),                      // 現在時刻を設定
            'updated_at' => now(),                      // 現在時刻を設定
        ];
    }
}
