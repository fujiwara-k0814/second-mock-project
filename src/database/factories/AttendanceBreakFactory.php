<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use App\Models\Attendance;

class AttendanceBreakFactory extends Factory
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
        $startTime = '12:00:00';
        $endTime = '13:00:00';

        return [
            'attendance_id' => Attendance::factory(),
            'break_start' => Carbon::parse($date->format('Y-m-d') . ' ' . $startTime),
            'break_end' => Carbon::parse($date->format('Y-m-d') . ' ' . $endTime),
        ];
    }
}
