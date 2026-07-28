<?php

namespace Database\Factories;

use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * Advanced:
 * 読書計画のファクトリー
 */
class ReadingPlanFactory extends Factory
{
    /**
     * 読書計画reading_plansのモデルファクトリー定義
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'book_id' => Book::factory(),
            'start_date' => Carbon::today()->subDays(2)->toDateString(),
            'target_date' => Carbon::today()->addDays(3)->toDateString(),
            'status' => ReadingPlanStatus::Inactive->value,
            'completed_at' => null,
        ];
    }

    /**
     * ’読書中'のenumを返す関数
     */
    public function reading(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ReadingPlanStatus::Active,
        ]);
    }
}
