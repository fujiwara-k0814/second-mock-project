<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Database\Seeders\UsersTableSeeder;
use Database\Seeders\AdminsTableSeeder;
use App\Models\User;
use App\Models\Admin;
use Illuminate\Support\Carbon;
use App\Models\Attendance;
use App\Models\AttendanceBreak;

class GetAdminAttendanceListTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(UsersTableSeeder::class);
        $this->seed(AdminsTableSeeder::class);
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    //管理者 勤怠一覧 全表示
    public function test_admin_attendance_list_display_all()
    {
        //テストユーザー1 勤怠情報
        $firstUserDay = [
            'user_id' => 1,
            'date' => Carbon::now(),
            'clock_in' => Carbon::now()->setTime(9, 0),
            'clock_out' => Carbon::now()->setTime(18, 0),
            'comment' => null,
            'attendance_id' => 1,
            'break_start' => Carbon::now()->setTime(12, 0),
            'break_end' => Carbon::now()->setTime(13, 0),
            'total_break_seconds' => 3600,
            'actual_work_seconds' => 28800,
            'user_name' => '認証済ユーザー',
        ];
        Attendance::create([
            'user_id' => $firstUserDay['user_id'],
            'date' => $firstUserDay['date'],
            'clock_in' => $firstUserDay['clock_in'],
            'clock_out' => $firstUserDay['clock_out'],
            'comment' => $firstUserDay['comment'],
        ]);
        AttendanceBreak::create([
            'attendance_id' => $firstUserDay['attendance_id'],
            'break_start' => $firstUserDay['break_start'],
            'break_end' => $firstUserDay['break_end'],
        ]);

        //テストユーザー2 勤怠情報
        $secondUserDay = [
            'user_id' => 2,
            'date' => Carbon::now(),
            'clock_in' => Carbon::now()->setTime(8, 0),
            'clock_out' => Carbon::now()->setTime(17, 0),
            'comment' => null,
            'attendance_id' => 2,
            'break_start' => Carbon::now()->setTime(12, 0),
            'break_end' => Carbon::now()->setTime(14, 0),
            'total_break_seconds' => 7200,
            'actual_work_seconds' => 25200,
            'user_name' => '未認証ユーザー',
        ];
        Attendance::create([
            'user_id' => $secondUserDay['user_id'],
            'date' => $secondUserDay['date'],
            'clock_in' => $secondUserDay['clock_in'],
            'clock_out' => $secondUserDay['clock_out'],
            'comment' => $secondUserDay['comment'],
        ]);
        AttendanceBreak::create([
            'attendance_id' => $secondUserDay['attendance_id'],
            'break_start' => $secondUserDay['break_start'],
            'break_end' => $secondUserDay['break_end'],
        ]);

        $admin = Admin::find(1);
        $response = $this->actingAs($admin, 'admin')->get('/admin/attendance/list');

        $response->assertStatus(200);
        $response->assertViewHas('attendances',
            function ($attendances) use ($firstUserDay, $secondUserDay) {
                return $attendances[0]->id === $firstUserDay['attendance_id']
                    && $attendances[0]->user_id === $firstUserDay['user_id']
                    && $attendances[0]->user->name === $firstUserDay['user_name']
                    && $attendances[0]->date
                        ->isoFormat('MM/DD(ddd)') === 
                        $firstUserDay['date']
                        ->isoFormat('MM/DD(ddd)')
                    && $attendances[0]->clock_in->Format('H:i') === $firstUserDay['clock_in']->Format('H:i')
                    && $attendances[0]->clock_out->Format('H:i') === $firstUserDay['clock_out']->Format('H:i')
                    && $attendances[0]->comment === $firstUserDay['comment']
                    && $attendances[0]->total_break_seconds === $firstUserDay['total_break_seconds']
                    && $attendances[0]->actual_work_seconds === $firstUserDay['actual_work_seconds']

                    && $attendances[1]->id === $secondUserDay['attendance_id']
                    && $attendances[1]->user_id === $secondUserDay['user_id']
                    && $attendances[1]->user->name === $secondUserDay['user_name']
                    && $attendances[1]->date
                        ->isoFormat('MM/DD(ddd)') === 
                        $secondUserDay['date']
                        ->isoFormat('MM/DD(ddd)')
                    && $attendances[1]->clock_in->Format('H:i') === $secondUserDay['clock_in']->Format('H:i')
                    && $attendances[1]->clock_out->Format('H:i') === $secondUserDay['clock_out']->Format('H:i')
                    && $attendances[1]->comment === $secondUserDay['comment']
                    && $attendances[1]->total_break_seconds === $secondUserDay['total_break_seconds']
                    && $attendances[1]->actual_work_seconds === $secondUserDay['actual_work_seconds'];
            }
        );
    }

    //管理者 勤怠一覧 現在日付表示
    public function test_admin_attendance_list_display_current_date()
    {
        $user = User::find(1);
        $user->attendances()->create([
            'date' => Carbon::now(),
            'clock_in' => Carbon::now(),
            'clock_out' => Carbon::now(),
        ]);

        $admin = Admin::find(1);
        $response = $this->actingAs($admin, 'admin')->get('/admin/attendance/list');

        $response->assertStatus(200);
        $response->assertViewHas('targetDate', function ($targetDate) {
            return $targetDate->isoFormat('YYYY/MM/DD') === Carbon::now()->isoFormat('YYYY/MM/DD');
        });
    }

    //管理者 勤怠一覧 前日表示
    public function test_admin_attendance_list_display_previous_day()
    {
        //前日勤怠
        $prevDate = Carbon::now()->subDay();
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
            'user_name' => '認証済ユーザー',
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

        $admin = Admin::find(1);
        $response = $this->actingAs($admin, 'admin')->get('/admin/attendance/list');

        $response->assertStatus(200);

        $response = $this->actingAs($admin, 'admin')
            ->get("/admin/attendance/list/$prevDate->year/$prevDate->month/$prevDate->day");

        $response->assertViewHas('targetDate', function ($targetDate) use ($prevDate) {
            return $targetDate->isoFormat('YYYY/MM/DD') === $prevDate->isoFormat('YYYY/MM/DD');
        });
        $response->assertViewHas('attendances',
            function ($attendances) use ($prev) {
                return $attendances[0]->id === $prev['attendance_id']
                    && $attendances[0]->user_id === $prev['user_id']
                    && $attendances[0]->user->name === $prev['user_name']
                    && $attendances[0]->date->isoFormat('MM/DD(ddd)') === $prev['date']->isoFormat('MM/DD(ddd)')
                    && $attendances[0]->clock_in->Format('H:i') === $prev['clock_in']->Format('H:i')
                    && $attendances[0]->clock_out->Format('H:i') === $prev['clock_out']->Format('H:i')
                    && $attendances[0]->comment === $prev['comment']
                    && $attendances[0]->total_break_seconds === $prev['total_break_seconds']
                    && $attendances[0]->actual_work_seconds === $prev['actual_work_seconds'];
            }
        );
    }

    //管理者 勤怠一覧 翌日表示
    public function test_admin_attendance_list_display_next_day()
    {
        //前日勤怠
        $nextDate = Carbon::now()->addDay();
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
            'user_name' => '認証済ユーザー',
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

        $admin = Admin::find(1);
        $response = $this->actingAs($admin, 'admin')->get('/admin/attendance/list');

        $response->assertStatus(200);

        $response = $this->actingAs($admin, 'admin')
            ->get("/admin/attendance/list/$nextDate->year/$nextDate->month/$nextDate->day");

        $response->assertViewHas('targetDate', function ($targetDate) use ($nextDate) {
            return $targetDate->isoFormat('YYYY/MM/DD') === $nextDate->isoFormat('YYYY/MM/DD');
        });
        $response->assertViewHas('attendances',
            function ($attendances) use ($next) {
                return $attendances[0]->id === $next['attendance_id']
                    && $attendances[0]->user_id === $next['user_id']
                    && $attendances[0]->user->name === $next['user_name']
                    && $attendances[0]->date->isoFormat('MM/DD(ddd)') === $next['date']->isoFormat('MM/DD(ddd)')
                    && $attendances[0]->clock_in->Format('H:i') === $next['clock_in']->Format('H:i')
                    && $attendances[0]->clock_out->Format('H:i') === $next['clock_out']->Format('H:i')
                    && $attendances[0]->comment === $next['comment']
                    && $attendances[0]->total_break_seconds === $next['total_break_seconds']
                    && $attendances[0]->actual_work_seconds === $next['actual_work_seconds'];
            }
        );
    }
}
