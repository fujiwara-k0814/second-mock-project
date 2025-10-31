<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Attendance;

class AmendmentApplicationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $faker = \Faker\Factory::create('ja_JP');

        $startTime = '10:00:00';
        $endTime = '19:00:00';

        return [
            'attendance_id' => Attendance::factory(),
            'approval_status_id' => $faker->numberBetween(1, 2),
            'new_clock_in_time' => $startTime,
            'new_clock_out_time' => $endTime,
            'new_comment' => $faker->boolean() ? '体調不良のため' : null,
        ];
    }
}
