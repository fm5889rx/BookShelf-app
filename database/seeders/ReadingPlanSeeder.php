<?php

namespace Database\Seeders;

use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Advanced:
 * 読書計画のseed
 */
class ReadingPlanSeeder extends Seeder
{
    public function run(): void
    {
        $desired = 9;          // 生成したいレコード数

        $created = 0;         // 成功した回数

        while ($created < $desired) {

            // ランダムにユーザー・本を取得
            if ($created < 5){
                $user = User::find(1);

            } else {
                $user = User::find(2);
            }

            $book = Book::inRandomOrder()->first();

            // firstOrCreate で重複を自動で回避
            $plan = ReadingPlan::firstOrCreate(
                [
                    'user_id' => $user->id,

                    'book_id' => $book->id,
                ],
                [
                    'start_date' => Carbon::today()->subDays(rand(0, 5)),

                    'target_date'=> Carbon::today()->addDays(rand(5, 10)),

                    'status' => ReadingPlanStatus::INACTIVE,
                ],
            );

            switch ($created) {

                case 0:
                    $plan->status = ReadingPlanStatus::NOPLAN;

                    $plan->save();

                    break;

                case 1:
                    $plan->status = ReadingPlanStatus::INACTIVE;

                    $plan->save();

                    break;

                case 2:
                    $plan->status = ReadingPlanStatus::ACTIVE;

                    $plan->save();

                    break;

                case 3:
                    $plan->status = ReadingPlanStatus::COMPLETE;

                    $plan->save();

                    break;

                case 4:
                    $plan->status = ReadingPlanStatus::PAUSE;

                    $plan->save();

                    break;

                default:
                    $cases = ReadingPlanStatus::cases();

                    $random = $cases[array_rand($cases)];

                    $plan->status = $random->value;

                    $plan->save();

                    break;
            }

            // 既に存在した場合は firstOrCreate がそのまま返すので、成功したかどうかを判定する
            if ($plan->wasRecentlyCreated) {
                $created++;
            }
        }
    }
}
