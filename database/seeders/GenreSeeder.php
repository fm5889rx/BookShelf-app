<?php

namespace Database\Seeders;

use App\Models\Genre;
use Illuminate\Database\Seeder;

class GenreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Genre::firstOrCreate([                              // 1件目の固定データ
            'name' => '小説',
        ]);

        Genre::firstOrCreate([                              // 2件目の固定データ
            'name' => 'ビジネス',
        ]);

        Genre::firstOrCreate([                              // 3件目の固定データ
            'name' => '技術書',
        ]);

        Genre::firstOrCreate([                              // 4件目の固定データ
            'name' => '自己啓発',
        ]);

        Genre::firstOrCreate([                              // 5件目の固定データ
            'name' => 'エッセイ',
        ]);

        Genre::firstOrCreate([                              // 6件目の固定データ
            'name' => '歴史',
        ]);

        Genre::firstOrCreate([                              // 7件目の固定データ
            'name' => '科学',
        ]);

        Genre::firstOrCreate([                              // 8件目の固定データ
            'name' => '芸術',
        ]);
        Genre::firstOrCreate([                              // 9件目の固定データ
            'name' => '料理',
        ]);

        Genre::firstOrCreate([                              // 10件目の固定データ
            'name' => '旅行',
        ]);
    }
}
