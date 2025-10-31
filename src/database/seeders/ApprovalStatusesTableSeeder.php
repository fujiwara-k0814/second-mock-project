<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ApprovalStatus;

class ApprovalStatusesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $params = [
            [
                'code' => '承認待ち',
            ],
            [
                'code' => '承認済み',
            ],
        ];

        $range = count($params);
        for ($i = 0; $i < $range; $i++) {
            ApprovalStatus::create($params[$i]);
        }
    }
}
