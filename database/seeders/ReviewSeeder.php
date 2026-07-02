<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 32; $i++) {                  // 生成件数回繰り返し
            $review = Review::Create([                  // レコード新規作成
                'user_id' => random_int(1, 5),          // 1〜5のランダムなユーザーID
                'rating' => random_int(3, 5),           // 3〜5のランダムな評価点
                'comment' => '',                        // 一旦コメントは空にする
                'book_id' => random_int(1, 11),         // 1〜11のランダムな書籍ID
            ]);

            // 各回で具体的なレビューを入れていく
            switch ($i) {
                case 1:
                    $review->comment = 'この本はとても面白かったです！';
                    $review->save();
                    break;
                case 2:
                    $review->comment = '結構内容が難しかったです。';
                    $review->save();
                    break;
                case 3:
                    $review->comment = '初心者向けとあるが、内容が薄すぎて物足りない。';
                    $review->save();
                    break;
                case 4:
                    $review->comment = '自己啓発本によくある内容で、既視感があった。';
                    $review->save();
                    break;
                case 5:
                    $review->comment = '伏線回収が鮮やかで、最後まで一気読みした。';
                    $review->save();
                    break;
                case 6:
                    $review->comment = '登場人物の心理描写がリアルで胸が痛くなる。';
                    $review->save();
                    break;
                case 7:
                    $review->comment = '読んだ後、明日から新しいことに挑戦したくなった。';
                    $review->save();
                    break;
                case 8:
                    $review->comment = '日常の小さな幸せに気づかせてくれる温かい物語。';
                    $review->save();
                    break;
                case 9:
                    $review->comment = 'モチベーションが下がった時に何度も読み返したい。';
                    $review->save();
                    break;
                case 10:
                    $review->comment = '専門知識が図解付きで分かりやすく解説されている。';
                    $review->save();
                    break;
                case 11:
                    $review->comment = '具体的な事例が豊富で、すぐに仕事で実践できる。';
                    $review->save();
                    break;
                case 12:
                    $review->comment = '要点が簡潔にまとまっており、タイパが良い本。';
                    $review->save();
                    break;
                case 13:
                    $review->comment = 'データと根拠がしっかりしており、説得力が抜群。';
                    $review->save();
                    break;
                case 14:
                    $review->comment = 'コミュニケーションの基本を、改めて学べる良書。';
                    $review->save();
                    break;
                case 15:
                    $review->comment = '価格以上の価値がある、濃厚な情報量に大満足。';
                    $review->save();
                    break;
                case 16:
                    $review->comment = '世界観の作り込みが深く、ファンタジー好きに最適。';
                    $review->save();
                    break;
                case 17:
                    $review->comment = '短いエッセイ集なので、隙間時間の読書にぴったり。';
                    $review->save();
                    break;
                case 18:
                    $review->comment = 'まるで自分が旅をしているような臨場感を楽しめる。';
                    $review->save();
                    break;
                case 19:
                    $review->comment = '複雑な現代社会の本質を、鋭く切り取った名著。';
                    $review->save();
                    break;
                case 20:
                    $review->comment = '歴史の裏側が緻密に描かれており、知的好奇心が満ちる。';
                    $review->save();
                    break;
                case 21:
                    $review->comment = '図案や写真が綺麗で、パラパラめくるだけでも楽しい。';
                    $review->save();
                    break;
                case 22:
                    $review->comment = '著者の独自の視点が新鮮で、視野が大きく広がった。';
                    $review->save();
                    break;
                case 23:
                    $review->comment = '図表が多く、文章を読むのが苦手な人にもおすすめ。';
                    $review->save();
                    break;
                case 24:
                    $review->comment = '思考の整理に役立つフレームワークが学べる。';
                    $review->save();
                    break;
                case 25:
                    $review->comment = '全編を通して論理的で、非常に納得感がある。';
                    $review->save();
                    break;
                case 26:
                    $review->comment = '文字量が多いため、じっくり時間をかけて読みたい本。';
                    $review->save();
                    break;
                case 27:
                    $review->comment = '業界の裏事情がリアルに描かれていて興味深い。';
                    $review->save();
                    break;
                case 28:
                    $review->comment = 'マインドセットを変えるための具体的なステップが明確。';
                    $review->save();
                    break;
                case 29:
                    $review->comment = '問題提起は素晴らしいが、解決策が抽象的すぎる。';
                    $review->save();
                    break;
                case 30:
                    $review->comment = '具体的な事例が豊富で、すぐに仕事で実践できる。';
                    $review->save();
                    break;
                case 31:
                    $review->comment = '文字が小さく、レイアウトが少し読みづらかった。';
                    $review->save();
                    break;
                case 32:
                    $review->comment = '著者の独自の視点が新鮮で、視野が大きく広がった。';
                    $review->save();
                    break;
                default:
                    break;                          // 何も書き込まない
            }
        }
    }
}
