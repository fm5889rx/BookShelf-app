<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Review;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bookCount = Book::all()->count();                      // 登録書籍数を取得

        for ($bookId = 1; $bookId <= $bookCount; $bookId++) {   // 書籍数回繰り返し

            $reviewCount = random_int(2, 4);                    // 生成レビュー数をランダムに算出

            for ($j = 1; $j <= $reviewCount; $j++) {            // 書籍順にレビューを生成

                $review = Review::Create([                      // レコード新規作成
                    'user_id' => random_int(1, 5),              // 1〜5のランダムなユーザーID
                    'rating' => random_int(1, 5),               // 1〜5のランダムな評価点
                    'comment' => '',                            // 一旦コメントは空にする
                    'book_id' => $bookId,                       // 1〜11のランダムな書籍ID
                ]);

                // 各回で具体的なレビューを入れていく
                switch ($review->rating) {                      // 評価点ごとに汎用コメントを割り当てる

                    case 1:                                     // 評価点１
                        $review->comment = '期待していただけに、少し残念な内容でした。';

                        $review->save();                        // コメントを保存

                        break;                                  // 次の処理に移行する

                    case 2:                                     // 評価点２
                        $review->comment = 'ターゲットや好みが分かれる印象でした。';

                        $review->save();                        // コメントを保存

                        break;                                  // 次の処理に移行する

                    case 3:                                     // 評価点３
                        $review->comment = '基本が網羅されており、気軽に読めます。';

                        $review->save();                        // コメントを保存
                        break;                                  // 次の処理に移行する
                    case 4:                                     // 評価点４
                        $review->comment = '非常に参考になり、読んで損はない内容です。';

                        $review->save();                        // コメントを保存

                        break;                                  // 次の処理に移行する

                    case 5:                                     // 評価点５
                        $review->comment = '期待以上の満足感で、一気に読み終えました。';

                        $review->save();                        // コメントを保存

                        break;                                  // 次の処理に移行する

                    default:                                    // 1〜５以外
                        break;                                  // 何も書き込まない
                }
            }
        }
    }
}
