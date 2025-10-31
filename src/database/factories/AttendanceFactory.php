<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use Illuminate\Support\Carbon;

class AttendanceFactory extends Factory
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
        $startTime = '9:00:00';
        $endTime = '18:00:00';

        return [
            'user_id' => User::factory(),
            'date' => $date,
            'clock_in' => Carbon::parse($date->format('Y-m-d') . ' ' . $startTime),
            'clock_out' => Carbon::parse($date->format('Y-m-d') . ' ' . $endTime),
            'comment' => $faker->boolean() ? '電車遅延のため' : null,
        ];
    }
}
