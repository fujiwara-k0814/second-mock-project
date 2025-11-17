<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Database\Seeders\UsersTableSeeder;
use App\Models\User;
use Illuminate\Support\Carbon;
use App\Enums\AttendanceStatus;

class ClockOutTest extends TestCase
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
    //退勤機能 退勤ボタン
    public function test_clock_out_button()
    {
        $user = User::find(1);
        $user->attendances()->create([
            'date' => Carbon::now(),
            'clock_in' => Carbon::now(),
            'clock_out' => null,
        ]);
        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('出勤中');
        $response->assertSee('退勤');

        //退勤済分岐の為、'finish'付与
        $response = $this->followingRedirects()->actingAs($user)->post('/attendance', [
            'status' => AttendanceStatus::WORKING->value,
            'finish' => null,
        ]);

        $response->assertSee('退勤済');
    }

    //退勤機能 勤怠一覧画面で確認
    public function test_clock_out_confirming_with_attendance_list()
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

        //現在時刻から1時間後に退勤操作
        $clockOutTime = Carbon::setTestNow(Carbon::now()->addHour());

        //退勤済分岐の為、'finish'付与
        $this->followingRedirects()->actingAs($user)->post('/attendance', [
            'status' => AttendanceStatus::WORKING->value,
            'finish' => null,
        ]);

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee(Carbon::now()->locale('ja')->isoFormat('MM/DD(ddd)'));
        $response->assertSee($clockOutTime);
    }
}
