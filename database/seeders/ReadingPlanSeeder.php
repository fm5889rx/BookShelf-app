<?php

namespace Database\Seeders;

use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use PDOException;

/**
 * Advanced:
 * 読書計画のseed
 */
class ReadingPlanSeeder extends Seeder
{
    public function run(): void
    {
        $desired = 9;                                       // 生成したいレコード数

        $created = 0;                                       // 成功した回数

        while ($created < $desired) {                       // 指定件数に到達するまで繰り返す

            if ($created < 5){                              // 生成レコード数が5件以下のとき

                $user = User::find(1);                      // ユーザー１の情報を取得

            } else {                                        // ６件目以降

                $user = User::find(2);                      // ユーザー2の情報を取得

            }

            $book = Book::inRandomOrder()->first();         // ランダムな書籍情報を取得

            try {                                           // exeption監視

                $plan = ReadingPlan::firstOrCreate(         // firstOrCreate で重複を自動で回避
                    [
                        'user_id' => $user->id,             // ユーザーIDをセット

                        'book_id' => $book->id,             // 書籍IDをセット

                        'start_date' => Carbon::today()->subDays(rand(0, 5)), // Seeder実行日から
                                                                        // ランダムに過去の日付をセット
                        'target_date'=> Carbon::today()->addDays(rand(5, 10)), // Seeder実行日から
                                                                        // ランダムに未来の日付をセット

                        'status' => ReadingPlanStatus::INACTIVE, // ステータス初期値は未読書とする
                    ],
                );
            } catch (PDOException $e) {                     // SQL-exeption発生

                if ($e->getCode() == 23000) {               // user_idとbook＿idの組み合わせがユニークで
                                                            // 時にSQLSTATE(23000)が発生
                    continue;                               // 以下の処理をスキップして次回ループを実行
                }
            }


            switch ($created) {                             // 生成件数によってステータスの綾井を変える

                case 0:                                     // レコード1
                    $plan->status = ReadingPlanStatus::NOPLAN; // ステータスを未計画に書き換える

                    $plan->save();                          // レコードに保存

                    break;                                  // 次の処理へ

                case 1:                                     // レコード2
                    $plan->status = ReadingPlanStatus::INACTIVE; // ステータスを未読書に書き換える

                    $plan->save();                          // レコードに保存

                    break;                                  // 次の処理へ

                case 2:                                     // レコード3
                    $plan->status = ReadingPlanStatus::ACTIVE; // ステータスを読書中に書き換える

                    $plan->save();                          // レコードに保存

                    break;                                  // 次の処理へ

                case 3:                                     // レコード4
                    $plan->status = ReadingPlanStatus::COMPLETE; // ステータスを読了に書き換える

                    $plan->save();                          // レコードに保存

                    break;                                  // 次の処理へ

                case 4:                                     // レコード5
                    $plan->status = ReadingPlanStatus::PAUSE; // ステータスを一時中断に書き換える

                    $plan->save();                          // レコードに保存

                    break;                                  // 次の処理へ

                default:                                    // それ以外
                    $cases = ReadingPlanStatus::cases();    // ステータスenum配列を取得

                    $random = $cases[array_rand($cases)];   // 配列からランダムにステータスを取得

                    $plan->status = $random->value;         // ステータスをランダムなステータスに書き換える

                    $plan->save();                          // レコードに保存

                    break;                                  // 次の処理へ
            }

            // 既に存在した場合は firstOrCreate がそのまま返すので、成功したかどうかを判定する
            if ($plan->wasRecentlyCreated) {

                $created++;                                 // 作成済みカウントを更新
    
            }
        }
    }
}
