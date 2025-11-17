<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
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

        $date = Carbon::parse($faker->date());
        $startTime = '10:00:00';
        $endTime = '19:00:00';

        return [
            'attendance_id' => Attendance::factory(),
            'approval_status_id' => $faker->numberBetween(1, 2),
            'date' => $date,
            'clock_in' => Carbon::parse($date->format('Y-m-d') . ' ' . $startTime),
            'clock_out' => Carbon::parse($date->format('Y-m-d') . ' ' . $endTime),
            'comment' => $faker->boolean() ? '体調不良のため' : null,
        ];
    }
}
