<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Seeder;

class BookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();                           // 登録済みユーザ情報を一括で取得

        $a1 = Book::firstOrCreate([                     // 1件目の初期データ
            'title' => '吾輩は猫である',
            'author' => '夏目漱石',
            'isbn' => '9784101010014',
            'published_date' => '1905-01-01',
            'description' => '夏目漱石のデビュー作にして代表作である長編小説です。名前を持たない猫の視点から、飼い主の英語教師やその周囲に集まる文化人たちの滑稽な日常を観察し、人間社会を痛烈に風刺した作品として知られています。',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475597?text=1',
            'user_id' => $users->random()->id,          // ランダムにユーザーIDを割り当て
        ]);
        $a1->genres()->sync([1]);                       // ピボットテーブルに書き込み

        $a1 = Book::firstOrCreate([                     // 2件目の初期データ
            'title' => '人を動かす',
            'author' => 'D・カーネギー',
            'isbn' => '9784422100524',
            'published_date' => '1936-10-01',
            'description' => '1936年の初版以来、世界中で読み継がれている不朽の自己啓発書です。人を批判せず、相手の重要感を満たし、自発的に動きたくなるような人間関係の「原理原則」を実例とともに解説しています。',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475597?text=2',
            'user_id' => $users->random()->id,          // ランダムにユーザーIDを割り当て
        ]);
        $a1->genres()->sync([2, 4]);                    // ピボットテーブルに書き込み

        $a1 = Book::firstOrCreate([                     // 3件目の初期データ
            'title' => 'リーダブルコード',
            'author' => 'Dustin Boswell',
            'isbn' => '9784873115658',
            'published_date' => '2012-06-23',
            'description' => '他人が最短時間で理解できる「読みやすいコード」を書くためのシンプルで実践的なテクニックをまとめた、ソフトウェア開発者の世界的ベストセラー書籍です。',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475597?text=3',
            'user_id' => $users->random()->id,          // ランダムにユーザーIDを割り当て
        ]);
        $a1->genres()->syncWithoutDetaching([3]);       // ピボットテーブルに書き込み

        $a1 = Book::firstOrCreate([                     // 4件目の初期データ
            'title' => '7つの習慣',
            'author' => 'スティーブン・R・コヴィー',
            'isbn' => '9784863940246',
            'published_date' => '2013-08-30',
            'description' => 'スティーブン・R・コヴィー博士によって提唱された世界的ベストセラーです。一時的なテクニックではなく、長期的に成功し続け、真の充実した人生を送るための普遍的な原則（人格主義）を体系化したものです。',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475597?text=4',
            'user_id' => $users->random()->id,          // ランダムにユーザーIDを割り当て
        ]);
        $a1->genres()->sync([2, 4]);                    // ピボットテーブルに書き込み

        $a1 = Book::firstOrCreate([                     // 5件目の初期データ
            'title' => '坊っちゃん',
            'author' => '夏目漱石',
            'isbn' => '9784101010021',
            'published_date' => '1906-04-01',
            'description' => '1906年に発表された日本の代表的な青春小説です。無鉄砲で曲がったことが大嫌いな江戸っ子の主人公が、四国の中学校に数学教師として赴任し、ずる賢い同僚たちと大騒動を繰り広げる痛快な物語です。',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475597?text=5',
            'user_id' => $users->random()->id,          // ランダムにユーザーIDを割り当て
        ]);
        $a1->genres()->sync([1]);                       // ピボットテーブルに書き込み

        $a1 = Book::firstOrCreate([                     // 6件目の初期データ
            'title' => 'サピエンス全史',
            'author' => 'ユヴァル・ノア・ハラリ',
            'isbn' => '9784309226712',
            'published_date' => '2016-09-08',
            'description' => '非力な野生動物だったホモ・サピエンスが、いかにして想像力を駆使して協力し合い、地球の支配者にまで上り詰めたのかを、生物学や歴史学など多角的な視点から解き明かした世界的ベストセラーです。',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475597?text=6',
            'user_id' => $users->random()->id,          // ランダムにユーザーIDを割り当て
        ]);
        $a1->genres()->sync([6, 7]);                    // ピボットテーブルに書き込み

        $a1 = Book::firstOrCreate([                     // 7件目の初期データ
            'title' => 'Clean Code',
            'author' => 'Robert C. Martin',
            'isbn' => '9784048930598',
            'published_date' => '2017-12-18',
            'description' => '「読みやすく、保守しやすい、変更に強いコード（＝クリーンコード）」を書くための普遍的な原則と実践テクニックを解説した、世界中のプログラマーのバイブルとも言える名著です',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475597?text=7',
            'user_id' => $users->random()->id,          // ランダムにユーザーIDを割り当て
        ]);
        $a1->genres()->sync([3]);                       // ピボットテーブルに書き込み

        $a1 = Book::firstOrCreate([                     // 8件目の初期データ
            'title' => '嫌われる勇気',
            'author' => '岸見一郎・古賀史健',
            'isbn' => '9784478025819',
            'published_date' => '2013-12-13',
            'description' => '心理学者アルフレッド・アドラーの思想「アドラー心理学」を、悩み多き青年と哲人の対話形式でわかりやすく解説した世界的ベストセラーの自己啓発書です。「すべての悩みは対人関係にある」とし、他者の視線から解放されて自由に生きる方法を説いています。',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475597?text=8',
            'user_id' => $users->random()->id,          // ランダムにユーザーIDを割り当て
        ]);
        $a1->genres()->sync([4]);                       // ピボットテーブルに書き込み

        $a1 = Book::firstOrCreate([                     // 9件目の初期データ
            'title' => '火花',
            'author' => '又吉直樹',
            'isbn' => '9784163902302',
            'published_date' => '2015-03-11',
            'description' => 'お笑いコンビ・ピースの又吉直樹による初の小説作品です。売れない若手芸人の「徳永」が、天才肌の先輩芸人「神谷」に出会い、お笑い哲学や生き方を学びながら自らの葛藤と成長を経ていく姿を描いた青春小説です。',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475597?text=9',
            'user_id' => $users->random()->id,          // ランダムにユーザーIDを割り当て
        ]);
        $a1->genres()->sync([1]);                       // ピボットテーブルに書き込み

        $a1 = Book::firstOrCreate([                     // 10件目の初期データ
            'title' => 'FACTFULNESS',
            'author' => 'ハンス・ロスリング',
            'isbn' => '9784822289607',
            'published_date' => '2019-01-11',
            'description' => '『FACTFULNESS（ファクトフルネス）』とは、データや事実に基づき、感情や思い込みに左右されず正しく世界を読み解く習慣のことです。ハンス・ロスリングらが著した世界的ベストセラーであり、人間が陥りやすい10の思い込みから解放されるための方法を解説しています。',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475597?text=10',
            'user_id' => $users->random()->id,          // ランダムにユーザーIDを割り当て
        ]);
        $a1->genres()->syncWithoutDetaching([2, 7]);    // ピボットテーブルに書き込み

        $a1 = Book::firstOrCreate([                     // 11件目の初期データ
            'title' => 'コンテナ物語',
            'author' => 'マルク・レビンソン',
            'isbn' => '9784822251468',
            'published_date' => '2007-01-18',
            'description' => '規格化された「鉄の箱」であるコンテナがいかにして誕生し、世界中の物流を根本から変え、グローバル経済の基盤を築き上げたのかを描いた経済・歴史ノンフィクションです。',
            'image_url' => 'https://placehold.co/200x300/e2e8f0/475597?text=11',
            'user_id' => $users->random()->id,          // ランダムにユーザーIDを割り当て
        ]);
        $a1->genres()->sync([2, 6]);                    // ピボットテーブルに書き込み

    }
}
