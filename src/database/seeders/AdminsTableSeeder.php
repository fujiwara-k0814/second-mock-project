<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AdminsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //管理者1
        $param = [
            'email' => 'admin1@example.com',
            'password' => Hash::make('password'),
        ];
        Admin::create($param);

        //管理者2
        $param = [
            'email' => 'admin2@example.com',
            'password' => Hash::make('password'),
        ];
        Admin::create($param);
    }
}
