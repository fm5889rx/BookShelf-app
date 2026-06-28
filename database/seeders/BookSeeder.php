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
        $a1 = Book::firstOrCreate([                     // 1件目の初期データ
            'title' => '吾輩は猫である',
            'author' => '夏目漱石',
            'isbn' => '9784101010014',
            'published_date' => '1905-01-01',
            'description' => '',
            'image_url' => 'https://placehold.co/200x300/e2e8f0475597?text=1',
            'user_id' => User::first()->id,
        ]);
        $a1->genres()->sync([1]);                       // ピボットテーブルに書き込み

        $a1 = Book::firstOrCreate([                     // 2件目の初期データ
            'title' => '人を動かす',
            'author' => 'D・カーネギー',
            'isbn' => '9784422100524',
            'published_date' => '1936-10-01',
            'description' => '',
            'image_url' => 'https://placehold.co/200x300/e2e8f0475597?text=2',
            'user_id' => User::first()->id,
        ]);
        $a1->genres()->sync([2, 4]);                    // ピボットテーブルに書き込み

        $a1 = Book::firstOrCreate([                     // 3件目の初期データ
            'title' => 'リーダブルコード',
            'author' => 'Dustin Boswell',
            'isbn' => '9784873115658',
            'published_date' => '2012-06-23',
            'description' => '',
            'image_url' => 'https://placehold.co/200x300/e2e8f0475597?text=3',
            'user_id' => User::first()->id,
        ]);
        $a1->genres()->syncWithoutDetaching([3]);       // ピボットテーブルに書き込み

        $a1 = Book::firstOrCreate([                     // 4件目の初期データ
            'title' => '7つの習慣',
            'author' => 'スティーブン・R・コヴィー',
            'isbn' => '9784863940246',
            'published_date' => '2013-08-30',
            'description' => '',
            'image_url' => 'https://placehold.co/200x300/e2e8f0475597?text=4',
            'user_id' => User::first()->id,
        ]);
        $a1->genres()->sync([2, 4]);                    // ピボットテーブルに書き込み

        $a1 = Book::firstOrCreate([                     // 5件目の初期データ
            'title' => '坊っちゃん',
            'author' => '夏目漱石',
            'isbn' => '9784101010021',
            'published_date' => '1906-04-01',
            'description' => '',
            'image_url' => 'https://placehold.co/200x300/e2e8f0475597?text=5',
            'user_id' => User::first()->id,
        ]);
        $a1->genres()->sync([1]);                       // ピボットテーブルに書き込み

        $a1 = Book::firstOrCreate([                     // 6件目の初期データ
            'title' => 'サピエンス全史',
            'author' => 'ユヴァル・ノア・ハラリ',
            'isbn' => '9784309226712',
            'published_date' => '2016-09-08',
            'description' => '',
            'image_url' => 'https://placehold.co/200x300/e2e8f0475597?text=6',
            'user_id' => User::first()->id,
        ]);
        $a1->genres()->sync([6, 7]);                    // ピボットテーブルに書き込み

        $a1 = Book::firstOrCreate([                     // 7件目の初期データ
            'title' => 'Clean Code',
            'author' => 'Robert C. Martin',
            'isbn' => '9784048930598',
            'published_date' => '2017-12-18',
            'description' => '',
            'image_url' => 'https://placehold.co/200x300/e2e8f0475597?text=7',
            'user_id' => User::first()->id,
        ]);
        $a1->genres()->sync([3]);                       // ピボットテーブルに書き込み

        $a1 = Book::firstOrCreate([                     // 8件目の初期データ
            'title' => '嫌われる勇気',
            'author' => '岸見一郎・古賀史健',
            'isbn' => '9784478025819',
            'published_date' => '2013-12-13',
            'description' => '',
            'image_url' => 'https://placehold.co/200x300/e2e8f0475597?text=8',
            'user_id' => User::first()->id,
        ]);
        $a1->genres()->sync([4]);                       // ピボットテーブルに書き込み

        $a1 = Book::firstOrCreate([                     // 9件目の初期データ
            'title' => '火花',
            'author' => '又吉直樹',
            'isbn' => '9784163902302',
            'published_date' => '2015-03-11',
            'description' => '',
            'image_url' => 'https://placehold.co/200x300/e2e8f0475597?text=9',
            'user_id' => User::first()->id,
        ]);
        $a1->genres()->sync([1]);                       // ピボットテーブルに書き込み

        $a1 = Book::firstOrCreate([                     // 10件目の初期データ
            'title' => 'FACTFULNESS',
            'author' => 'ハンス・ロスリング',
            'isbn' => '9784822289607',
            'published_date' => '2019-01-11',
            'description' => '',
            'image_url' => 'https://placehold.co/200x300/e2e8f0475597?text=10',
            'user_id' => User::first()->id,
        ]);
        $a1->genres()->syncWithoutDetaching([2, 7]);    // ピボットテーブルに書き込み

        $a1 = Book::firstOrCreate([                     // 11件目の初期データ
            'title' => 'コンテナ物語',
            'author' => 'マルク・レビンソン',
            'isbn' => '9784822251468',
            'published_date' => '2007-01-18',
            'description' => '',
            'image_url' => 'https://placehold.co/200x300/e2e8f0475597?text=11',
            'user_id' => User::first()->id,
        ]);
        $a1->genres()->sync([2, 6]);                    // ピボットテーブルに書き込み

    }
}
