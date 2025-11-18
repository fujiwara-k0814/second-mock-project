<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Database\Seeders\UsersTableSeeder;
use App\Models\User;
use Illuminate\Support\Carbon;
use App\Models\AttendanceBreak;

//ID:10 勤怠詳細情報取得機能（一般ユーザー）
class GetUserAttendanceDetailTest extends TestCase
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
    //勤怠詳細 名前
    public function test_user_attendance_detail_display_name()
    {
        $targetUser = User::find(1);

        $attendance = $targetUser->attendances()->create([
            'date' => Carbon::now(),
            'clock_in' => Carbon::now(),
            'clock_out' => Carbon::now(),
        ]);

        $response = $this->actingAs($targetUser)->get("/attendance/detail/$attendance->id");

        $response->assertStatus(200);

        $response->assertViewHas('displayAttendance',function ($displayAttendance) use ($attendance) {
                return $displayAttendance->id === $attendance->id;
            }
        );

        $response->assertViewHas('user',function ($user) use ($targetUser) {
                return $user->name === $targetUser->name;
            }
        );
    }

    //勤怠詳細 日付
    public function test_user_attendance_detail_display_date()
    {
        $user = User::find(1);

        $attendance = $user->attendances()->create([
            'date' => Carbon::now(),
            'clock_in' => Carbon::now(),
            'clock_out' => Carbon::now(),
        ]);

        $response = $this->actingAs($user)->get("/attendance/detail/$attendance->id");

        $response->assertStatus(200);

        $response->assertViewHas('displayAttendance',
            function ($displayAttendance) use ($attendance) {
                return $displayAttendance->id === $attendance->id
                    && $displayAttendance->date
                        ->locale('ja')
                        ->isoFormat('YYYY年') ===
                        $attendance->date
                        ->locale('ja')
                        ->isoFormat('YYYY年')
                    && $displayAttendance->date
                        ->locale('ja')
                        ->isoFormat('M月D日') ===
                        $attendance->date
                        ->locale('ja')
                        ->isoFormat('M月D日');
            }
        );
    }

    //勤怠詳細 出勤・退勤
    public function test_user_attendance_detail_display_clock_in_and_clock_out()
    {
        $user = User::find(1);

        $attendance = $user->attendances()->create([
            'date' => Carbon::now(),
            'clock_in' => Carbon::now()->setTime(9, 0),
            'clock_out' => Carbon::now()->setTime(18, 0),
        ]);

        $response = $this->actingAs($user)->get("/attendance/detail/$attendance->id");

        $response->assertStatus(200);

        $response->assertViewHas('displayAttendance',
            function ($displayAttendance) use ($attendance) {
                return $displayAttendance->id === $attendance->id
                    && $displayAttendance->clock_in->Format('H:i') === $attendance->clock_in->Format('H:i')
                    && $displayAttendance->clock_out->Format('H:i') === $attendance->clock_out->Format('H:i');
            }
        );
    }

    //勤怠詳細 休憩
    public function test_user_attendance_detail_display_break()
    {
        $user = User::find(1);

        $attendance = $user->attendances()->create([
            'date' => Carbon::now(),
            'clock_in' => Carbon::now()->setTime(9, 0),
            'clock_out' => Carbon::now()->setTime(18, 0),
        ]);

        $firstBreak = AttendanceBreak::create([
            'attendance_id' => $attendance->id,
            'break_start' => Carbon::now()->setTime(12, 0),
            'break_end' => Carbon::now()->setTime(13, 0),
        ]);

        $secondBreak = AttendanceBreak::create([
            'attendance_id' => $attendance->id,
            'break_start' => Carbon::now()->setTime(15, 0),
            'break_end' => Carbon::now()->setTime(15, 30),
        ]);

        $response = $this->actingAs($user)->get("/attendance/detail/$attendance->id");

        $response->assertStatus(200);

        $response->assertViewHas('displayAttendance',function ($displayAttendance) use ($attendance) {
                return $displayAttendance->id === $attendance->id;
            }
        );

        $response->assertViewHas('breaks',
            function ($breaks) use ($firstBreak, $secondBreak) {
                return $breaks[0]->break_start->Format('H:i') === $firstBreak->break_start->Format('H:i')
                    && $breaks[0]->break_end->Format('H:i') === $firstBreak->break_end->Format('H:i')
                    && $breaks[1]->break_start->Format('H:i') === $secondBreak->break_start->Format('H:i')
                    && $breaks[1]->break_end->Format('H:i') === $secondBreak->break_end->Format('H:i');
            }
        );
    }
}
