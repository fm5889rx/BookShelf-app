<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::firstOrCreate([                               // 1件目の初期ユーザー情報
            'name' => '山田太郎',
            'email' => 'yamada@example.com',
            'password' => Hash::make('password'),
        ]);

        User::firstOrCreate([                               // 2件目の初期ユーザー情報
            'name' => '鈴木花子',
            'email' => 'suzuki@example.com',
            'password' => Hash::make('password'),
        ]);

        User::firstOrCreate([                               // 3件目の初期ユーザー情報
            'name' => '田中一郎',
            'email' => 'tanaka@example.com',
            'password' => Hash::make('password'),
        ]);

        User::firstOrCreate([                               // 4件目の初期ユーザー情報
            'name' => '佐藤美咲',
            'email' => 'sato@example.com',
            'password' => Hash::make('password'),
        ]);

        User::firstOrCreate([                               // 5件目の初期ユーザー情報
            'name' => '高橋健太',
            'email' => 'takahashi@example.com',
            'password' => Hash::make('password'),
        ]);
    }
}
