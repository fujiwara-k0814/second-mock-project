<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Database\Seeders\UsersTableSeeder;
use App\Models\User;
use Illuminate\Support\Carbon;
use App\Models\Attendance;
use App\Models\AttendanceBreak;

//ID:9 勤怠一覧情報取得機能（一般ユーザー）
class GetUserAttendanceListTest extends TestCase
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
    //勤怠一覧 勤怠情報全表示
    public function test_user_attendance_list_display_all()
    {
        $user = User::find(1);

        //当月の月初勤怠
        $firstDay = [
            'user_id' => 1,
            'date' => Carbon::now()->startOfMonth(),
            'clock_in' => Carbon::now()->startOfMonth()->setTime(9, 0),
            'clock_out' => Carbon::now()->startOfMonth()->setTime(18, 0),
            'comment' => null,
            'attendance_id' => 1,
            'break_start' => Carbon::now()->startOfMonth()->setTime(12, 0),
            'break_end' => Carbon::now()->startOfMonth()->setTime(13, 0),
            'total_break_seconds' => 3600,
            'actual_work_seconds' => 28800,
        ];
        Attendance::create([
            'user_id' => $firstDay['user_id'],
            'date' => $firstDay['date'],
            'clock_in' => $firstDay['clock_in'],
            'clock_out' => $firstDay['clock_out'],
            'comment' => $firstDay['comment'],
        ]);
        AttendanceBreak::create([
            'attendance_id' => $firstDay['attendance_id'],
            'break_start' => $firstDay['break_start'],
            'break_end' => $firstDay['break_end'],
        ]);

        //当月の月末勤怠
        $lastDay = [
            'user_id' => 1,
            'date' => Carbon::now()->endOfMonth(),
            'clock_in' => Carbon::now()->endOfMonth()->setTime(8, 0),
            'clock_out' => Carbon::now()->endOfMonth()->setTime(17, 0),
            'comment' => null,
            'attendance_id' => 2,
            'break_start' => Carbon::now()->endOfMonth()->setTime(12, 0),
            'break_end' => Carbon::now()->endOfMonth()->setTime(14, 0),
            'total_break_seconds' => 7200,
            'actual_work_seconds' => 25200,
        ];
        Attendance::create([
            'user_id' => $lastDay['user_id'],
            'date' => $lastDay['date'],
            'clock_in' => $lastDay['clock_in'],
            'clock_out' => $lastDay['clock_out'],
            'comment' => $lastDay['comment'],
        ]);
        AttendanceBreak::create([
            'attendance_id' => $lastDay['attendance_id'],
            'break_start' => $lastDay['break_start'],
            'break_end' => $lastDay['break_end'],
        ]);

        $response = $this->actingAs($user)->get('/attendance/list');
        
        $response->assertStatus(200);
        $response->assertViewHas('attendances', 
            function ($attendances) use ($firstDay, $lastDay) {
                return $attendances[0]->id === $firstDay['attendance_id']
                    && $attendances[0]->user_id === $firstDay['user_id']
                    && $attendances[0]->date->isoFormat('MM/DD(ddd)') === $firstDay['date']->isoFormat('MM/DD(ddd)')
                    && $attendances[0]->clock_in->Format('H:i') === $firstDay['clock_in']->Format('H:i')
                    && $attendances[0]->clock_out->Format('H:i') === $firstDay['clock_out']->Format('H:i')
                    && $attendances[0]->comment === $firstDay['comment']
                    && $attendances[0]->total_break_seconds === $firstDay['total_break_seconds']
                    && $attendances[0]->actual_work_seconds === $firstDay['actual_work_seconds']

                    && $attendances[1]->id === $lastDay['attendance_id']
                    && $attendances[1]->user_id === $lastDay['user_id']
                    && $attendances[1]->date->isoFormat('MM/DD(ddd)') === $lastDay['date']->isoFormat('MM/DD(ddd)')
                    && $attendances[1]->clock_in->Format('H:i') === $lastDay['clock_in']->Format('H:i')
                    && $attendances[1]->clock_out->Format('H:i') === $lastDay['clock_out']->Format('H:i')
                    && $attendances[1]->comment === $lastDay['comment']
                    && $attendances[1]->total_break_seconds === $lastDay['total_break_seconds']
                    && $attendances[1]->actual_work_seconds === $lastDay['actual_work_seconds'];
            });
    }

    //勤怠一覧 現在月表示
    public function test_user_attendance_list_display_current_month()
    {
        $user = User::find(1);
        $user->attendances()->create([
            'date' => Carbon::now(),
            'clock_in' => Carbon::now(),
            'clock_out' => Carbon::now(),
        ]);

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertViewHas('targetDate', function ($targetDate) {
            return $targetDate->isoFormat('YYYY/MM') === Carbon::now()->isoFormat('YYYY/MM');
        });
    }

    //勤怠一覧 前月表示
    public function test_attendance_list_display_previous_month()
    {
        $user = User::find(1);

        //前月勤怠
        $prevDate = Carbon::now()->subMonth();
        $prev = [
            'user_id' => 1,
            'date' => $prevDate,
            'clock_in' => $prevDate->copy()->setTime(9, 0),
            'clock_out' => $prevDate->copy()->setTime(18, 0),
            'comment' => null,
            'attendance_id' => 1,
            'break_start' => $prevDate->copy()->setTime(12, 0),
            'break_end' => $prevDate->copy()->setTime(13, 0),
            'total_break_seconds' => 3600,
            'actual_work_seconds' => 28800,
        ];
        Attendance::create([
            'user_id' => $prev['user_id'],
            'date' => $prev['date'],
            'clock_in' => $prev['clock_in'],
            'clock_out' => $prev['clock_out'],
            'comment' => $prev['comment'],
        ]);
        AttendanceBreak::create([
            'attendance_id' => $prev['attendance_id'],
            'break_start' => $prev['break_start'],
            'break_end' => $prev['break_end'],
        ]);

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);

        $response = $this->actingAs($user)->get("/attendance/list/$prevDate->year/$prevDate->month");

        $response->assertViewHas('targetDate', function ($targetDate) use ($prevDate) {
            return $targetDate->isoFormat('YYYY/MM') === $prevDate->isoFormat('YYYY/MM');
        });
        $response->assertViewHas(
            'attendances',
            function ($attendances) use ($prev) {
                return $attendances[0]->id === $prev['attendance_id']
                    && $attendances[0]->user_id === $prev['user_id']
                    && $attendances[0]->date->isoFormat('MM/DD(ddd)') === $prev['date']->isoFormat('MM/DD(ddd)')
                    && $attendances[0]->clock_in->Format('H:i') === $prev['clock_in']->Format('H:i')
                    && $attendances[0]->clock_out->Format('H:i') === $prev['clock_out']->Format('H:i')
                    && $attendances[0]->comment === $prev['comment']
                    && $attendances[0]->total_break_seconds === $prev['total_break_seconds']
                    && $attendances[0]->actual_work_seconds === $prev['actual_work_seconds'];
            }
        );
    }

    //勤怠一覧 翌月表示
    public function test_user_attendance_list_display_next_month()
    {
        $user = User::find(1);

        //翌月勤怠
        $nextDate = Carbon::now()->subMonth();
        $next = [
            'user_id' => 1,
            'date' => $nextDate,
            'clock_in' => $nextDate->copy()->setTime(9, 0),
            'clock_out' => $nextDate->copy()->setTime(18, 0),
            'comment' => null,
            'attendance_id' => 1,
            'break_start' => $nextDate->copy()->setTime(12, 0),
            'break_end' => $nextDate->copy()->setTime(13, 0),
            'total_break_seconds' => 3600,
            'actual_work_seconds' => 28800,
        ];
        Attendance::create([
            'user_id' => $next['user_id'],
            'date' => $next['date'],
            'clock_in' => $next['clock_in'],
            'clock_out' => $next['clock_out'],
            'comment' => $next['comment'],
        ]);
        AttendanceBreak::create([
            'attendance_id' => $next['attendance_id'],
            'break_start' => $next['break_start'],
            'break_end' => $next['break_end'],
        ]);

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);

        $response = $this->actingAs($user)->get("/attendance/list/$nextDate->year/$nextDate->month");

        $response->assertViewHas('targetDate', function ($targetDate) use ($nextDate) {
            return $targetDate->isoFormat('YYYY/MM') === $nextDate->isoFormat('YYYY/MM');
        });
        $response->assertViewHas(
            'attendances',
            function ($attendances) use ($next) {
                return $attendances[0]->id === $next['attendance_id']
                    && $attendances[0]->user_id === $next['user_id']
                    && $attendances[0]->date->isoFormat('MM/DD(ddd)') === $next['date']->isoFormat('MM/DD(ddd)')
                    && $attendances[0]->clock_in->Format('H:i') === $next['clock_in']->Format('H:i')
                    && $attendances[0]->clock_out->Format('H:i') === $next['clock_out']->Format('H:i')
                    && $attendances[0]->comment === $next['comment']
                    && $attendances[0]->total_break_seconds === $next['total_break_seconds']
                    && $attendances[0]->actual_work_seconds === $next['actual_work_seconds'];
            }
        );
    }

    //勤怠一覧 詳細表示
    public function test_user_attendance_list_display_attendance_detail()
    {
        $user = User::find(1);

        $attendance = $user->attendances()->create([
            'date' => Carbon::now(),
            'clock_in' => Carbon::now(),
            'clock_out' => Carbon::now(),
        ]);

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);

        $response = $this->actingAs($user)->get("/attendance/detail/$attendance->id");

        $response->assertStatus(200);
        $response->assertViewHas('displayAttendance',
            function ($displayAttendance) use ($attendance) {
                return $displayAttendance->id === $attendance->id;
            }
        );
    }
}
