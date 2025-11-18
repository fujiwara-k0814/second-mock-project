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
use App\Models\AmendmentApplicationBreak;
use App\Enums\ApplicationStatus;
use Illuminate\Support\Facades\DB;

//ID:15 勤怠情報修正機能（管理者）
class ApproveAttendanceAsAdminTest extends TestCase
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
    //管理者 修正申請 承認待ち
    public function test_admin_correction_request_pending()
    {
        //1件目
        $firstUser = User::find(1);
        $firstAttendance = $firstUser->attendances()->create([
            'date' => Carbon::now()->startOfMonth(),
            'clock_in' => Carbon::now()->startOfMonth()->setTime(9, 0),
            'clock_out' => Carbon::now()->startOfMonth()->setTime(18, 0),
            'comment' => null,
        ]);
        AttendanceBreak::create([
            'attendance_id' => $firstAttendance->id,
            'break_start' => Carbon::now()->startOfMonth()->setTime(12, 0),
            'break_end' => Carbon::now()->startOfMonth()->setTime(13, 0),
        ]);
        $firstApplication = AmendmentApplication::create([
            'attendance_id' => $firstAttendance->id,
            'approval_status_id' => ApplicationStatus::PENDING->value,
            'date' => Carbon::now()->startOfMonth(),
            'clock_in' => Carbon::now()->startOfMonth()->setTime(10, 0),
            'clock_out' => Carbon::now()->startOfMonth()->setTime(19, 0),
            'comment' => '電車遅延のため',
        ]);
        AmendmentApplicationBreak::create([
            'amendment_application_id' => $firstApplication->id,
            'break_start' => Carbon::now()->startOfMonth()->setTime(12, 30),
            'break_end' => Carbon::now()->startOfMonth()->setTime(13, 30),
        ]);

        //2件目
        $secondUser = User::find(2);
        $secondAttendance = $secondUser->attendances()->create([
            'date' => Carbon::now()->startOfMonth(),
            'clock_in' => Carbon::now()->startOfMonth()->setTime(8, 0),
            'clock_out' => Carbon::now()->startOfMonth()->setTime(17, 0),
            'comment' => null,
        ]);
        AttendanceBreak::create([
            'attendance_id' => $secondAttendance->id,
            'break_start' => Carbon::now()->startOfMonth()->setTime(11, 30),
            'break_end' => Carbon::now()->startOfMonth()->setTime(12, 30),
        ]);
        $secondApplication = AmendmentApplication::create([
            'attendance_id' => $secondAttendance->id,
            'approval_status_id' => ApplicationStatus::PENDING->value,
            'date' => Carbon::now()->startOfMonth(),
            'clock_in' => Carbon::now()->startOfMonth()->setTime(9, 30),
            'clock_out' => Carbon::now()->startOfMonth()->setTime(18, 30),
            'comment' => '体調不良のため',
        ]);
        AmendmentApplicationBreak::create([
            'amendment_application_id' => $secondApplication->id,
            'break_start' => Carbon::now()->startOfMonth()->setTime(12, 20),
            'break_end' => Carbon::now()->startOfMonth()->setTime(13, 20),
        ]);

        $admin = Admin::find(1);
        $response = $this->actingAs($admin, 'admin')
            ->get('admin/stamp_correction_request/list?tab=pending');

        $response->assertStatus(200);
        $response->assertViewHas('applications',
            function ($applications) use ($firstApplication, $firstUser, $secondApplication, $secondUser) {
                return $applications[0]->id === $firstApplication->id
                    && $applications[0]->attendance->user->name === $firstUser->name
                    && $applications[0]->date
                        ->isoFormat('YYYY/MM/DD') ===
                        $firstApplication->date
                        ->isoFormat('YYYY/MM/DD')
                    && $applications[0]->comment === $firstApplication->comment
                    && $applications[0]->created_at
                        ->Format('YYYY/MM/DD') ===
                        $firstApplication->created_at
                        ->Format('YYYY/MM/DD')
                    && $applications[1]->id === $secondApplication->id
                    && $applications[1]->attendance->user->name === $secondUser->name
                    && $applications[1]->date
                        ->isoFormat('YYYY/MM/DD') ===
                        $secondApplication->date
                        ->isoFormat('YYYY/MM/DD')
                    && $applications[1]->comment === $secondApplication->comment
                    && $applications[1]->created_at
                        ->Format('YYYY/MM/DD') ===
                        $secondApplication->created_at
                        ->Format('YYYY/MM/DD');
            }
        );
    }

    //管理者 修正申請 承認済み
    public function test_admin_correction_request_approved()
    {
        //1件目
        $firstUser = User::find(1);
        $firstAttendance = $firstUser->attendances()->create([
            'date' => Carbon::now()->startOfMonth(),
            'clock_in' => Carbon::now()->startOfMonth()->setTime(9, 0),
            'clock_out' => Carbon::now()->startOfMonth()->setTime(18, 0),
            'comment' => null,
        ]);
        AttendanceBreak::create([
            'attendance_id' => $firstAttendance->id,
            'break_start' => Carbon::now()->startOfMonth()->setTime(12, 0),
            'break_end' => Carbon::now()->startOfMonth()->setTime(13, 0),
        ]);
        $firstApplication = AmendmentApplication::create([
            'attendance_id' => $firstAttendance->id,
            'approval_status_id' => ApplicationStatus::APPROVED->value,
            'date' => Carbon::now()->startOfMonth(),
            'clock_in' => Carbon::now()->startOfMonth()->setTime(10, 0),
            'clock_out' => Carbon::now()->startOfMonth()->setTime(19, 0),
            'comment' => '電車遅延のため',
        ]);
        AmendmentApplicationBreak::create([
            'amendment_application_id' => $firstApplication->id,
            'break_start' => Carbon::now()->startOfMonth()->setTime(12, 30),
            'break_end' => Carbon::now()->startOfMonth()->setTime(13, 30),
        ]);

        //2件目
        $secondUser = User::find(2);
        $secondAttendance = $secondUser->attendances()->create([
            'date' => Carbon::now()->startOfMonth(),
            'clock_in' => Carbon::now()->startOfMonth()->setTime(8, 0),
            'clock_out' => Carbon::now()->startOfMonth()->setTime(17, 0),
            'comment' => null,
        ]);
        AttendanceBreak::create([
            'attendance_id' => $secondAttendance->id,
            'break_start' => Carbon::now()->startOfMonth()->setTime(11, 30),
            'break_end' => Carbon::now()->startOfMonth()->setTime(12, 30),
        ]);
        $secondApplication = AmendmentApplication::create([
            'attendance_id' => $secondAttendance->id,
            'approval_status_id' => ApplicationStatus::APPROVED->value,
            'date' => Carbon::now()->startOfMonth(),
            'clock_in' => Carbon::now()->startOfMonth()->setTime(9, 30),
            'clock_out' => Carbon::now()->startOfMonth()->setTime(18, 30),
            'comment' => '体調不良のため',
        ]);
        AmendmentApplicationBreak::create([
            'amendment_application_id' => $secondApplication->id,
            'break_start' => Carbon::now()->startOfMonth()->setTime(12, 20),
            'break_end' => Carbon::now()->startOfMonth()->setTime(13, 20),
        ]);

        $admin = Admin::find(1);
        $response = $this->actingAs($admin, 'admin')
            ->get('admin/stamp_correction_request/list?tab=approved');

        $response->assertStatus(200);
        $response->assertViewHas('applications',
            function ($applications) use ($firstApplication, $firstUser, $secondApplication, $secondUser) {
                return $applications[0]->id === $firstApplication->id
                    && $applications[0]->attendance->user->name === $firstUser->name
                    && $applications[0]->date
                        ->isoFormat('YYYY/MM/DD') ===
                        $firstApplication->date
                        ->isoFormat('YYYY/MM/DD')
                    && $applications[0]->comment === $firstApplication->comment
                    && $applications[0]->created_at
                        ->Format('YYYY/MM/DD') ===
                        $firstApplication->created_at
                        ->Format('YYYY/MM/DD')
                    && $applications[1]->id === $secondApplication->id
                    && $applications[1]->attendance->user->name === $secondUser->name
                    && $applications[1]->date
                        ->isoFormat('YYYY/MM/DD') ===
                        $secondApplication->date
                        ->isoFormat('YYYY/MM/DD')
                    && $applications[1]->comment === $secondApplication->comment
                    && $applications[1]->created_at
                        ->Format('YYYY/MM/DD') ===
                        $secondApplication->created_at
                        ->Format('YYYY/MM/DD');
            }
        );
    }

    //管理者 修正申請 詳細内容表示
    public function test_admin_correction_request_detail_display()
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
        $application = AmendmentApplication::create([
            'attendance_id' => $attendance->id,
            'approval_status_id' => ApplicationStatus::PENDING->value,
            'date' => Carbon::now(),
            'clock_in' => Carbon::now()->setTime(10, 0),
            'clock_out' => Carbon::now()->setTime(19, 0),
            'comment' => '電車遅延のため',
        ]);
        $applicationBreak = AmendmentApplicationBreak::create([
            'amendment_application_id' => $application->id,
            'break_start' => Carbon::now()->setTime(12, 30),
            'break_end' => Carbon::now()->setTime(13, 30),
        ]);

        $admin = Admin::find(1);
        $response = $this->actingAs($admin, 'admin')
            ->get("admin/stamp_correction_request/approve/$application->id");

        $response->assertStatus(200);
        $response->assertViewHas('amendment',
            function ($amendment) use ($application, $user) {
                return $amendment->id === $application->id
                    && $amendment->attendance->user->name === $user->name
                    && $amendment->date
                        ->locale('ja')
                        ->isoFormat('YYYY年') ===
                        $application->date
                        ->locale('ja')
                        ->isoFormat('YYYY年')
                    && $amendment->date
                        ->locale('ja')
                        ->isoFormat('MM月DD日') ===
                        $application->date
                        ->locale('ja')
                        ->isoFormat('MM月DD日')
                    && $amendment->clock_in->Format('H:i') === $application->clock_in->Format('H:i')
                    && $amendment->clock_out->Format('H:i') === $application->clock_out->Format('H:i')
                    && $amendment->comment === $application->comment;
            }
        );

        $response->assertViewHas('breaks',
            function ($breaks) use ($applicationBreak) {
                return $breaks[0]->id === $applicationBreak->id
                    && $breaks[0]->break_start->Format('H:i') === $applicationBreak->break_start->Format('H:i')
                    && $breaks[0]->break_end->Format('H:i') === $applicationBreak->break_end->Format('H:i');
            }
        );
    }

    //管理者 修正申請 承認処理
    public function test_admin_correction_request_approve()
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
        $application = AmendmentApplication::create([
            'attendance_id' => $attendance->id,
            'approval_status_id' => ApplicationStatus::PENDING->value,
            'date' => Carbon::now(),
            'clock_in' => Carbon::now()->setTime(10, 0),
            'clock_out' => Carbon::now()->setTime(19, 0),
            'comment' => '電車遅延のため',
        ]);
        AmendmentApplicationBreak::create([
            'amendment_application_id' => $application->id,
            'break_start' => Carbon::now()->setTime(12, 30),
            'break_end' => Carbon::now()->setTime(13, 30),
        ]);

        $admin = Admin::find(1);
        $response = $this->actingAs($admin, 'admin')
            ->get("admin/stamp_correction_request/approve/$application->id");

        $response->assertStatus(200);

        $this->actingAs($admin, 'admin')
            ->post("/admin/stamp_correction_request/approve/$application->id");

        $this->assertTrue(
            DB::table('attendances')
                ->whereDate('date', $application->date)
                ->whereTime('clock_in', $application->clock_in->format('H:i:s'))
                ->whereTime('clock_out', $application->clock_out->format('H:i:s'))
                ->where('comment', $application->comment)
                ->exists()
        );
    }
}
