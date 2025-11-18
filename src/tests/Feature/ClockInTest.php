<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Database\Seeders\UsersTableSeeder;
use App\Models\User;
use Illuminate\Support\Carbon;
use App\Enums\AttendanceStatus;

//ID:6 出勤機能
class ClockInTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(UsersTableSeeder::class);
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    //出勤機能 出勤ボタン
    public function test_clock_in_button()
    {
        $user = User::find(1);
        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('勤務外');
        $response->assertSee('出勤');

        $response = $this->followingRedirects()->actingAs($user)->post('/attendance', [
            'status' => AttendanceStatus::OFF->value,
        ]);

        $response->assertSee('出勤中');
    }

    //出勤機能 一日一回のみ
    public function test_clock_in_once_a_day()
    {
        $user = User::find(1);
        $user->attendances()->create([
            'date' => Carbon::now(),
            'clock_in' => Carbon::now(),
            'clock_out' => Carbon::now(),
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('退勤済');
        $response->assertDontSee('出勤');
    }

    //出勤機能 勤怠一覧画面で確認
    public function test_clock_in_confirming_with_attendance_list()
    {
        $user = User::find(1);
        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('勤務外');
        $response->assertSee('出勤');

        $this->followingRedirects()->actingAs($user)->post('/attendance', [
            'status' => AttendanceStatus::OFF->value,
        ]);

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee(Carbon::now()->locale('ja')->isoFormat('MM/DD(ddd)'));
        $response->assertSee(Carbon::now()->locale('ja')->isoFormat('HH:mm'));
    }
}
