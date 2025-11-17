<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Database\Seeders\UsersTableSeeder;
use Database\Seeders\AdminsTableSeeder;
use Database\Seeders\ApprovalStatusesTableSeeder;
use App\Models\User;
use App\Models\Admin;
use Illuminate\Support\Carbon;
use App\Models\AttendanceBreak;
use App\Models\AmendmentApplication;

class EditUserAttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(UsersTableSeeder::class);
        $this->seed(AdminsTableSeeder::class);
        $this->seed(ApprovalStatusesTableSeeder::class);
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    //勤怠修正 バリデーション 出勤時間が退勤時間より後
    public function test_user_attendance_edit_validate_clock_in()
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

        $response = $this->actingAs($user)->get("/attendance/detail/$attendance->id");

        $response->assertStatus(200);

        $response = $this->actingAs($user)->post("/attendance/detail/$attendance->id", [
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

    //勤怠修正 バリデーション 休憩開始時間が退勤時間より後
    public function test_user_attendance_edit_validate_break_start()
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

        $response = $this->actingAs($user)->get("/attendance/detail/$attendance->id");

        $response->assertStatus(200);

        $response = $this->actingAs($user)->post("/attendance/detail/$attendance->id", [
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

    //勤怠修正 バリデーション 休憩終了時間が退勤時間より後
    public function test_user_attendance_edit_validate_break_end()
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

        $response = $this->actingAs($user)->get("/attendance/detail/$attendance->id");

        $response->assertStatus(200);

        $response = $this->actingAs($user)->post("/attendance/detail/$attendance->id", [
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

    //勤怠修正 バリデーション 備考欄
    public function test_user_attendance_edit_validate_comment()
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

        $response = $this->actingAs($user)->get("/attendance/detail/$attendance->id");

        $response->assertStatus(200);

        $response = $this->actingAs($user)->post("/attendance/detail/$attendance->id", [
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

    //勤怠修正 申請処理
    public function test_user_attendance_edit()
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

        $response = $this->actingAs($user)->get("/attendance/detail/$attendance->id");

        $response->assertStatus(200);

        $this->actingAs($user)->post("/attendance/detail/$attendance->id", [
            'clock_in' => '9:00',
            'clock_out' => '18:00',
            'comment' => 'テスト',
            'break_start' => ['12:00'],
            'break_end' => ['13:00'],
        ]);

        $application = AmendmentApplication::where(
            'attendance_id', $attendance->id
            )
            ->with('attendance.user', 'amendmentApplicationBreaks')
            ->first();

        $this->post('/logout');
        $this->assertGuest();

        $admin = Admin::find(1);
        
        //申請一覧画面 管理者
        $response = $this->actingAs($admin, 'admin')->get('admin/stamp_correction_request/list?tab=pending');

        $response->assertStatus(200);

        $response->assertViewHas('applications',
            function ($applications) use ($application) {
                return $applications[0]->id === $application->id
                    && $applications[0]->attendance->user->name === $application->attendance->user->name
                    && $applications[0]->date
                        ->isoFormat('YYYY/MM/DD') === 
                        $application->date
                        ->isoFormat('YYYY/MM/DD')
                    && $applications[0]->comment === $application->comment
                    && $applications[0]->created_at
                        ->Format('YYYY/MM/DD') === 
                        $application->created_at
                        ->Format('YYYY/MM/DD');
            }
        );

        //承認画面 管理者
        $response = $this->actingAs($admin, 'admin')
            ->get("admin/stamp_correction_request/approve/$application->id");

        $response->assertStatus(200);

        $response->assertViewHas('amendment',
            function ($amendment) use ($application) {
                return $amendment->id === $application->id
                    && $amendment->attendance->user->name === $application->attendance->user->name
                    && $amendment->date
                        ->locale('ja')
                        ->isoFormat('YYYY年') === 
                        $application->date
                        ->locale('ja')
                        ->isoFormat('YYYY年')
                    && $amendment->date
                        ->locale('ja')
                        ->isoFormat('M月D日') === 
                        $application->date
                        ->locale('ja')
                        ->isoFormat('M月D日')
                    && $amendment->clock_in->Format('H:i') === $application->clock_in->Format('H:i')
                    && $amendment->clock_out->Format('H:i') === $application->clock_out->Format('H:i')
                    && $amendment->amendmentApplicationBreaks[0]->break_start
                        ->Format('H:i') === 
                        $application->amendmentApplicationBreaks[0]->break_start
                        ->Format('H:i')
                    && $amendment->amendmentApplicationBreaks[0]->break_end
                        ->Format('H:i') === 
                        $application->amendmentApplicationBreaks[0]->break_end
                        ->Format('H:i')
                    && $amendment->comment === $application->comment;
            }
        );
    }

    //勤怠修正 申請処理 承認待ち
    public function test_user_attendance_edit_pending()
    {
        $user = User::find(1);

        //1件目
        $firstAttendance = $user->attendances()->create([
            'date' => Carbon::now(),
            'clock_in' => Carbon::now()->setTime(9, 0),
            'clock_out' => Carbon::now()->setTime(18, 0),
            'comment' => null,
        ]);
        AttendanceBreak::create([
            'attendance_id' => $firstAttendance->id,
            'break_start' => Carbon::now()->setTime(12, 0),
            'break_end' => Carbon::now()->setTime(13, 0),
        ]);

        //2件目
        $secondAttendance = $user->attendances()->create([
            'date' => Carbon::now()->addDay(),
            'clock_in' => Carbon::now()->addDay()->setTime(10, 0),
            'clock_out' => Carbon::now()->addDay()->setTime(19, 0),
            'comment' => null,
        ]);
        AttendanceBreak::create([
            'attendance_id' => $secondAttendance->id,
            'break_start' => Carbon::now()->addDay()->setTime(12, 30),
            'break_end' => Carbon::now()->addDay()->setTime(13, 30),
        ]);

        //1件目申請
        $response = $this->actingAs($user)->get("/attendance/detail/$firstAttendance->id");
        $response->assertStatus(200);
        $this->actingAs($user)->post("/attendance/detail/$firstAttendance->id", [
            'clock_in' => '9:00',
            'clock_out' => '18:00',
            'comment' => 'テスト1',
            'break_start' => ['12:00'],
            'break_end' => ['13:00'],
        ]);

        //2件目申請
        $response = $this->actingAs($user)->get("/attendance/detail/$secondAttendance->id");
        $response->assertStatus(200);
        $this->actingAs($user)->post("/attendance/detail/$secondAttendance->id", [
            'clock_in' => '10:00',
            'clock_out' => '19:00',
            'comment' => 'テスト2',
            'break_start' => ['12:00'],
            'break_end' => ['13:00'],
        ]);

        $targetapplications = AmendmentApplication::with('attendance.user', 'amendmentApplicationBreaks')
            ->get();

        //承認待ち遷移
        $response = $this->actingAs($user)->get('/stamp_correction_request/list?tab=pending');
        $response->assertStatus(200);

        //[0]:1件目申請 [1]:2件目申請
        $response->assertViewHas('applications',
            function ($applications) use ($targetapplications) {
                return $applications[0]->id === $targetapplications[0]->id
                    && $applications[0]->attendance->user
                        ->name === 
                        $targetapplications[0]->attendance->user
                        ->name
                    && $applications[0]->date
                        ->isoFormat('YYYY/MM/DD') ===
                        $targetapplications[0]->date
                        ->isoFormat('YYYY/MM/DD')
                    && $applications[0]->comment === $targetapplications[0]->comment
                    && $applications[0]->created_at
                        ->Format('YYYY/MM/DD') ===
                        $targetapplications[0]->created_at
                        ->Format('YYYY/MM/DD')
                    && $applications[1]->id === $targetapplications[1]->id
                    && $applications[1]->attendance->user
                        ->name === 
                        $targetapplications[1]->attendance->user
                        ->name
                    && $applications[1]->date
                        ->isoFormat('YYYY/MM/DD') ===
                        $targetapplications[1]->date
                        ->isoFormat('YYYY/MM/DD')
                    && $applications[1]->comment === $targetapplications[1]->comment
                    && $applications[1]->created_at
                        ->Format('YYYY/MM/DD') ===
                        $targetapplications[1]->created_at
                        ->Format('YYYY/MM/DD');
            }
        );
    }

    //勤怠修正 申請処理 承認済み
    public function test_user_attendance_edit_approved()
    {
        $user = User::find(1);

        //1件目
        $firstAttendance = $user->attendances()->create([
            'date' => Carbon::now(),
            'clock_in' => Carbon::now()->setTime(9, 0),
            'clock_out' => Carbon::now()->setTime(18, 0),
            'comment' => null,
        ]);
        AttendanceBreak::create([
            'attendance_id' => $firstAttendance->id,
            'break_start' => Carbon::now()->setTime(12, 0),
            'break_end' => Carbon::now()->setTime(13, 0),
        ]);

        //2件目
        $secondAttendance = $user->attendances()->create([
            'date' => Carbon::now()->addDay(),
            'clock_in' => Carbon::now()->addDay()->setTime(10, 0),
            'clock_out' => Carbon::now()->addDay()->setTime(19, 0),
            'comment' => null,
        ]);
        AttendanceBreak::create([
            'attendance_id' => $secondAttendance->id,
            'break_start' => Carbon::now()->addDay()->setTime(12, 30),
            'break_end' => Carbon::now()->addDay()->setTime(13, 30),
        ]);

        //1件目申請
        $response = $this->actingAs($user)->get("/attendance/detail/$firstAttendance->id");
        $response->assertStatus(200);
        $this->actingAs($user)->post("/attendance/detail/$firstAttendance->id", [
            'clock_in' => '9:00',
            'clock_out' => '18:00',
            'comment' => 'テスト1',
            'break_start' => ['12:00'],
            'break_end' => ['13:00'],
        ]);

        //2件目申請
        $response = $this->actingAs($user)->get("/attendance/detail/$secondAttendance->id");
        $response->assertStatus(200);
        $this->actingAs($user)->post("/attendance/detail/$secondAttendance->id", [
            'clock_in' => '10:00',
            'clock_out' => '19:00',
            'comment' => 'テスト2',
            'break_start' => ['12:00'],
            'break_end' => ['13:00'],
        ]);

        $targetapplications = AmendmentApplication::with('attendance.user', 'amendmentApplicationBreaks')
            ->get();

        $this->post('/logout');
        $this->assertGuest();

        //承認画面 管理者
        $admin = Admin::find(1);

        //1件目承認
        $response = $this->actingAs($admin, 'admin')
            ->get("/admin/stamp_correction_request/approve/{$targetapplications[0]->id}");
        $response->assertStatus(200);
        $this->actingAs($admin, 'admin')
            ->post("/admin/stamp_correction_request/approve/{$targetapplications[0]->id}");

        //2件目承認
        $response = $this->actingAs($admin, 'admin')
            ->get("/admin/stamp_correction_request/approve/{$targetapplications[1]->id}");
        $response->assertStatus(200);
        $this->actingAs($admin, 'admin')
            ->post("/admin/stamp_correction_request/approve/{$targetapplications[1]->id}");

        //更新データ再取得
        $targetapplications = AmendmentApplication::with('attendance.user', 'amendmentApplicationBreaks')
            ->get();

        $this->post('/admin/logout');
        $this->assertGuest();

        //ユーザー切り替え(承認済み遷移)
        $response = $this->actingAs($user, 'web')->get('/stamp_correction_request/list?tab=approved');
        $response->assertStatus(200);

        //[0]:1件目申請 [1]:2件目申請
        $response->assertViewHas('applications',
            function ($applications) use ($targetapplications) {
                return $applications[0]->id === $targetapplications[0]->id
                    && $applications[0]->attendance->user
                    ->name ===
                    $targetapplications[0]->attendance->user
                    ->name
                    && $applications[0]->date
                    ->isoFormat('YYYY/MM/DD') ===
                    $targetapplications[0]->date
                    ->isoFormat('YYYY/MM/DD')
                    && $applications[0]->comment === $targetapplications[0]->comment
                    && $applications[0]->created_at
                    ->Format('YYYY/MM/DD') ===
                    $targetapplications[0]->created_at
                    ->Format('YYYY/MM/DD')
                    && $applications[1]->id === $targetapplications[1]->id
                    && $applications[1]->attendance->user
                    ->name ===
                    $targetapplications[1]->attendance->user
                    ->name
                    && $applications[1]->date
                    ->isoFormat('YYYY/MM/DD') ===
                    $targetapplications[1]->date
                    ->isoFormat('YYYY/MM/DD')
                    && $applications[1]->comment === $targetapplications[1]->comment
                    && $applications[1]->created_at
                    ->Format('YYYY/MM/DD') ===
                    $targetapplications[1]->created_at
                    ->Format('YYYY/MM/DD');
            }
        );
    }

    //勤怠修正 詳細画面遷移
    public function test_user_attendance_edit_detail_succession()
    {
        $user = User::find(1);

        //1件目
        $firstAttendance = $user->attendances()->create([
            'date' => Carbon::now(),
            'clock_in' => Carbon::now()->setTime(9, 0),
            'clock_out' => Carbon::now()->setTime(18, 0),
            'comment' => null,
        ]);
        AttendanceBreak::create([
            'attendance_id' => $firstAttendance->id,
            'break_start' => Carbon::now()->setTime(12, 0),
            'break_end' => Carbon::now()->setTime(13, 0),
        ]);

        //2件目
        $secondAttendance = $user->attendances()->create([
            'date' => Carbon::now()->addDay(),
            'clock_in' => Carbon::now()->addDay()->setTime(10, 0),
            'clock_out' => Carbon::now()->addDay()->setTime(19, 0),
            'comment' => null,
        ]);
        AttendanceBreak::create([
            'attendance_id' => $secondAttendance->id,
            'break_start' => Carbon::now()->addDay()->setTime(12, 30),
            'break_end' => Carbon::now()->addDay()->setTime(13, 30),
        ]);

        //1件目申請(承認済み)
        $response = $this->actingAs($user)->get("/attendance/detail/$firstAttendance->id");
        $response->assertStatus(200);
        $this->actingAs($user)->post("/attendance/detail/$firstAttendance->id", [
            'clock_in' => '9:00',
            'clock_out' => '18:00',
            'comment' => 'テスト1',
            'break_start' => ['12:00'],
            'break_end' => ['13:00'],
        ]);

        //2件目申請(承認待ち)
        $response = $this->actingAs($user)->get("/attendance/detail/$secondAttendance->id");
        $response->assertStatus(200);
        $this->actingAs($user)->post("/attendance/detail/$secondAttendance->id", [
            'clock_in' => '10:00',
            'clock_out' => '19:00',
            'comment' => 'テスト2',
            'break_start' => ['12:00'],
            'break_end' => ['13:00'],
        ]);

        $targetapplications = AmendmentApplication::with('attendance.user', 'amendmentApplicationBreaks')
            ->get();

        $this->post('/logout');
        $this->assertGuest();

        //承認画面 管理者
        $admin = Admin::find(1);

        //1件目承認
        $response = $this->actingAs($admin, 'admin')
            ->get("/admin/stamp_correction_request/approve/{$targetapplications[0]->id}");
        $response->assertStatus(200);
        $this->actingAs($admin, 'admin')
            ->post("/admin/stamp_correction_request/approve/{$targetapplications[0]->id}");

        //更新データ再取得
        $targetapplications = AmendmentApplication::with('attendance.user', 'amendmentApplicationBreaks')
            ->get();

        $this->post('/admin/logout');
        $this->assertGuest();


        //ユーザー切り替え(承認済み遷移)
        $response = $this->actingAs($user, 'web')->get('/stamp_correction_request/list?tab=approved');
        $response->assertStatus(200);
        //[0]:1件目申請(承認済み)
        $response->assertViewHas('applications',
            function ($applications) use ($targetapplications) {
                return $applications[0]->id === $targetapplications[0]->id;
            }
        );

        //詳細画面(承認済み)
        $response = $this->actingAs($user, 'web')->get("/attendance/detail/{$targetapplications[0]->id}");
        $response->assertStatus(200);
        //[0]:1件目申請(承認済み)
        $response->assertViewHas('displayAttendance',
            function ($displayAttendance) use ($targetapplications) {
                return $displayAttendance->id === $targetapplications[0]->id;
            }
        );


        //ユーザー切り替え(承認待ち遷移)
        $response = $this->actingAs($user, 'web')->get('/stamp_correction_request/list?tab=pending');
        $response->assertStatus(200);
        //[1]:2件目申請(承認待ち)
        $response->assertViewHas('applications',
            function ($applications) use ($targetapplications) {
                return $applications[0]->id === $targetapplications[1]->id;
            }
        );

        //詳細画面(承認待ち)
        $response = $this->actingAs($user, 'web')->get("/attendance/detail/{$targetapplications[1]->id}");
        $response->assertStatus(200);
        //[1]:2件目申請(承認待ち)
        $response->assertViewHas('displayAttendance',
            function ($displayAttendance) use ($targetapplications) {
                return $displayAttendance->id === $targetapplications[1]->id;
            }
        );
    }
}
