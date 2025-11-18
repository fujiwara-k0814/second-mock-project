<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //テスト用
        $param = [
            'name' => '認証済ユーザー',
            'email' => 'user1@example.com',
            'email_verified_at' => Carbon::now(),
            'password' => Hash::make('password'),
        ];
        User::create($param);

        $param = [
            'name' => '未認証ユーザー',
            'email' => 'user2@example.com',
            'email_verified_at' => null,
            'password' => Hash::make('password'),
        ];
        User::create($param);

        User::factory(5)->create();
    }
}
