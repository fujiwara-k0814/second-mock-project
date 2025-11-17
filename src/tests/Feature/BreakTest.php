<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Database\Seeders\UsersTableSeeder;
use App\Models\User;
use Illuminate\Support\Carbon;
use App\Enums\AttendanceStatus;

class BreakTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(UsersTableSeeder::class);

        $user = User::find(1);
        $user->attendances()->create([
            'date' => Carbon::now(),
            'clock_in' => Carbon::now(),
            'clock_out' => null,
        ]);
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    //休憩機能 休憩入りボタン
    public function test_break_on_break_button()
    {
        $user = User::find(1);
        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('出勤中');
        $response->assertSee('休憩入');

        $response = $this->followingRedirects()->actingAs($user)->post('/attendance', [
            'status' => AttendanceStatus::WORKING->value,
        ]);

        $response->assertSee('休憩中');
    }

    //休憩機能 休憩入りは一日に何回でもできる
    public function test_break_on_break_as_many_times_a_day()
    {
        $user = User::find(1);
        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('出勤中');
        $response->assertSee('休憩入');

        $response = $this->followingRedirects()->actingAs($user)->post('/attendance', [
            'status' => AttendanceStatus::WORKING->value,
        ]);

        $response->assertSee('休憩中');

        $response = $this->followingRedirects()->actingAs($user)->post('/attendance', [
            'status' => AttendanceStatus::ON_BREAK->value,
        ]);

        $response->assertSee('出勤中');
        $response->assertSee('休憩入');
    }

    //休憩機能 休憩戻りボタン
    public function test_break_break_back_button()
    {
        $user = User::find(1);
        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('出勤中');
        $response->assertSee('休憩入');

        $response = $this->followingRedirects()->actingAs($user)->post('/attendance', [
            'status' => AttendanceStatus::WORKING->value,
        ]);

        $response->assertSee('休憩戻');

        $response = $this->followingRedirects()->actingAs($user)->post('/attendance', [
            'status' => AttendanceStatus::ON_BREAK->value,
        ]);

        $response->assertSee('出勤中');
    }

    //休憩機能 休憩戻りは一日に何回でもできる
    public function test_break_break_back_as_many_times_a_day()
    {
        $user = User::find(1);
        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('出勤中');
        $response->assertSee('休憩入');

        $response = $this->followingRedirects()->actingAs($user)->post('/attendance', [
            'status' => AttendanceStatus::WORKING->value,
        ]);

        $response->assertSee('休憩戻');

        $response = $this->followingRedirects()->actingAs($user)->post('/attendance', [
            'status' => AttendanceStatus::ON_BREAK->value,
        ]);

        $response->assertSee('休憩入');

        $response = $this->followingRedirects()->actingAs($user)->post('/attendance', [
            'status' => AttendanceStatus::WORKING->value,
        ]);

        $response->assertSee('休憩戻');
    }

    //出勤機能 勤怠一覧画面で確認
    public function test_break_confirming_with_attendance_list()
    {
        $user = User::find(1);
        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('出勤中');
        $response->assertSee('休憩入');

        $response = $this->followingRedirects()->actingAs($user)->post('/attendance', [
            'status' => AttendanceStatus::WORKING->value,
        ]);

        $response->assertSee('休憩戻');

        //現在時刻から35分後の休憩戻り操作
        Carbon::setTestNow(Carbon::now()->addMinutes(35));

        $this->followingRedirects()->actingAs($user)->post('/attendance', [
            'status' => AttendanceStatus::ON_BREAK->value,
        ]);

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee(Carbon::now()->locale('ja')->isoFormat('MM/DD(ddd)'));
        $response->assertSee('0:35');
    }
}
