<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Database\Seeders\UsersTableSeeder;
use Database\Seeders\AdminsTableSeeder;
use App\Models\User;
use App\Models\Admin;
use Illuminate\Support\Carbon;
use App\Models\AttendanceBreak;

//ID:13 勤怠詳細情報取得・修正機能（管理者）
class AdminAttendanceDetailGetAndEditTest extends TestCase
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
    //管理者 勤怠詳細 表示
    public function test_admin_attendance_detail_display()
    {
        $targetUser = User::find(1);
        $attendance = $targetUser->attendances()->create([
            'date' => Carbon::now(),
            'clock_in' => Carbon::now()->setTime(9, 0),
            'clock_out' => Carbon::now()->setTime(18, 0),
            'comment' => null,
        ]);
        $break = AttendanceBreak::create([
            'attendance_id' => $attendance->id,
            'break_start' => Carbon::now()->setTime(12, 0),
            'break_end' => Carbon::now()->setTime(13, 0),
        ]);

        $admin = Admin::find(1);
        $response = $this->actingAs($admin, 'admin')->get("/admin/attendance/$attendance->id");

        $response->assertStatus(200);

        $response->assertViewHas('displayAttendance',
            function ($displayAttendance) use ($attendance) {
                return $displayAttendance->id === $attendance->id
                    && $displayAttendance->date
                        ->locale('ja')
                        ->isoFormat('YYYY年')  === 
                        $attendance->date
                        ->locale('ja')
                        ->isoFormat('YYYY年')
                    && $displayAttendance->date
                        ->locale('ja')
                        ->isoFormat('MM月DD日')  === 
                        $attendance->date
                        ->locale('ja')
                        ->isoFormat('MM月DD日')
                    && $displayAttendance->clock_in->Format('H:i') === $attendance->clock_in->Format('H:i')
                    && $displayAttendance->clock_out->Format('H:i') === $attendance->clock_out->Format('H:i')
                    && $displayAttendance->comment === $attendance->comment;
            }
        );

        $response->assertViewHas('user',
            function ($user) use ($targetUser) {
                return $user->name === $targetUser->name;
            }
        );

        $response->assertViewHas('breaks',
            function ($breaks) use ($break) {
                return $breaks[0]->attendance_id === $break->attendance_id
                    && $breaks[0]->break_start->Format('H:i') === $break->break_start->Format('H:i')
                    && $breaks[0]->break_end->Format('H:i') === $break->break_end->Format('H:i');
            }
        );
    }

    //管理者 勤怠修正 バリデーション 出勤時間が退勤時間より後
    public function test_admin_attendance_edit_validate_clock_in()
    {
        $user = User::find(1);
        $attendance = $user->attendances()->create([
            'date' => Carbon::now(),
            'clock_in' => Carbon::now()->setTime(9, 0),
            'clock_out' => Carbon::now()->setTime(18, 0),
            'comment' => null,
        ]);
        AttendanceBreak::create([
            'attendance_id' => $attendance->id,
            'break_start' => Carbon::now()->setTime(12, 0),
            'break_end' => Carbon::now()->setTime(13, 0),
        ]);

        $admin = Admin::find(1);
        $response = $this->actingAs($admin, 'admin')->get("/admin/attendance/$attendance->id");

        $response->assertStatus(200);

        $response = $this->actingAs($admin, 'admin')->post("/admin/attendance/$attendance->id", [
            'clock_in' => '19:00',
            'clock_out' => '18:00',
            'comment' => 'テスト',
            'break_start' => ['12:00'],
            'break_end' => ['13:00'],
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('clock_in');

        $errors = session('errors');
        $this->assertEquals('出勤時間もしくは退勤時間が不適切な値です', $errors->first('clock_in'));
    }

    //管理者 勤怠修正 バリデーション 休憩開始時間が退勤時間より後
    public function test_admin_attendance_edit_validate_break_start()
    {
        $user = User::find(1);
        $attendance = $user->attendances()->create([
            'date' => Carbon::now(),
            'clock_in' => Carbon::now()->setTime(9, 0),
            'clock_out' => Carbon::now()->setTime(18, 0),
            'comment' => null,
        ]);
        AttendanceBreak::create([
            'attendance_id' => $attendance->id,
            'break_start' => Carbon::now()->setTime(12, 0),
            'break_end' => Carbon::now()->setTime(13, 0),
        ]);

        $admin = Admin::find(1);
        $response = $this->actingAs($admin, 'admin')->get("/admin/attendance/$attendance->id");

        $response->assertStatus(200);

        $response = $this->actingAs($admin, 'admin')->post("/admin/attendance/$attendance->id", [
            'clock_in' => '9:00',
            'clock_out' => '18:00',
            'comment' => 'テスト',
            'break_start' => ['19:00'],
            'break_end' => ['13:00'],
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('break_start.0');

        $errors = session('errors');
        $this->assertEquals('休憩時間が不適切な値です', $errors->first('break_start.0'));
    }

    //管理者 勤怠修正 バリデーション 休憩終了時間が退勤時間より後
    public function test_admin_attendance_edit_validate_break_end()
    {
        $user = User::find(1);
        $attendance = $user->attendances()->create([
            'date' => Carbon::now(),
            'clock_in' => Carbon::now()->setTime(9, 0),
            'clock_out' => Carbon::now()->setTime(18, 0),
            'comment' => null,
        ]);
        AttendanceBreak::create([
            'attendance_id' => $attendance->id,
            'break_start' => Carbon::now()->setTime(12, 0),
            'break_end' => Carbon::now()->setTime(13, 0),
        ]);

        $admin = Admin::find(1);
        $response = $this->actingAs($admin, 'admin')->get("/admin/attendance/$attendance->id");

        $response->assertStatus(200);

        $response = $this->actingAs($user)->post("/admin/attendance/$attendance->id", [
            'clock_in' => '9:00',
            'clock_out' => '18:00',
            'comment' => 'テスト',
            'break_start' => ['12:00'],
            'break_end' => ['19:00'],
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('break_end.0');

        $errors = session('errors');
        $this->assertEquals('休憩時間もしくは退勤時間が不適切な値です', $errors->first('break_end.0'));
    }

    //管理者 勤怠修正 バリデーション 備考欄
    public function test_admin_attendance_edit_validate_comment()
    {
        $user = User::find(1);
        $attendance = $user->attendances()->create([
            'date' => Carbon::now(),
            'clock_in' => Carbon::now()->setTime(9, 0),
            'clock_out' => Carbon::now()->setTime(18, 0),
            'comment' => null,
        ]);
        AttendanceBreak::create([
            'attendance_id' => $attendance->id,
            'break_start' => Carbon::now()->setTime(12, 0),
            'break_end' => Carbon::now()->setTime(13, 0),
        ]);

        $admin = Admin::find(1);
        $response = $this->actingAs($admin, 'admin')->get("/admin/attendance/$attendance->id");

        $response->assertStatus(200);

        $response = $this->actingAs($admin, 'admin')->post("/admin/attendance/$attendance->id", [
            'clock_in' => '9:00',
            'clock_out' => '18:00',
            'comment' => null,
            'break_start' => ['12:00'],
            'break_end' => ['13:00'],
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('comment');

        $errors = session('errors');
        $this->assertEquals('備考を記入してください', $errors->first('comment'));
    }
}
