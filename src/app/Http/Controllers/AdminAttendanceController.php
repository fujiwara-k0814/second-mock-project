<?php

namespace App\Http\Controllers;

use Illuminate\Support\Carbon;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use App\Models\AmendmentApplication;
use App\Models\AmendmentApplicationBreak;
use App\Enums\ApplicationStatus;
use App\Http\Requests\StampCorrectionRequest;
use App\Services\AttendanceSummaryService;
use App\Services\AmendmentApplicationProcessor;
use App\Models\User;

class AdminAttendanceController extends Controller
{
    //ルート引数の初期値を'null'に指定
    public function index($year = null, $month = null, $day = null)
    {
        //'now()->**'省略時に現在年月を表示
        $targetDate = Carbon::createFromDate(
            $year ?? Carbon::now()->year,
            $month ?? Carbon::now()->month,
            $day ?? Carbon::now()->day,
        );
        $prev = $targetDate->copy()->subDay();
        $next = $targetDate->copy()->addDay();

        $attendances = collect();
        $users = User::all();

        //ユーザー空日付勤怠作成
        foreach ($users as $user) {
            $attendance = Attendance::firstOrCreate([
                'user_id' => $user->id,
                'date'    => $targetDate->toDateString(),
            ], [
                'clock_in' => null,
                'clock_out' => null,
            ]);
        }

        $attendances = Attendance::with('user', 'amendmentApplications')
            ->whereDate('date', $targetDate)
            ->orderBy('user_id')
            ->get();

        //プロパティ追加
        //総勤務 → 'total_work_seconds' 総休憩 → 'total_break_seconds' 総稼働 → 'actual_work_seconds'
        app(AttendanceSummaryService::class)->summarize($attendances);

        return view('admin.attendance-index', compact(
            'attendances', 
            'targetDate', 
            'prev', 
            'next'
        ));
    }

    public function edit($attendance_id)
    {
        //'Attendance','AmendmentApplication'の表示用のモデル共通変数として
        //'displayAttendance'を使用
        $displayAttendance = Attendance::with([
            'user',
            'latestAmendmentApplication.approvalStatus',
            'latestAmendmentApplication.amendmentApplicationBreaks'
        ])->find($attendance_id);

        $breaks = AttendanceBreak::where('attendance_id', $attendance_id)
            ->orderBy('break_start')->get();
            
        $attendanceId = $attendance_id;
        $user = $displayAttendance->user;
        $statusCode = $displayAttendance
            ->latestAmendmentApplication
            ?->approvalStatus
            ->code;

        //ステータスが承認待ちの場合のみ修正申請内容を'displayAttendance'に反映
        if ($statusCode === 'pending') {
            $displayAttendance = $displayAttendance->latestAmendmentApplication;
            $breaks = AmendmentApplicationBreak::where(
                'amendment_application_id', $displayAttendance->id
            )
            ->orderBy('break_start')
            ->get();
        }
        
        return view('shared.attendance-detail', compact(
            'displayAttendance',
            'attendanceId',
            'breaks',
            'user',
            'statusCode',
        ));
    }

    public function correction(StampCorrectionRequest $request, $attendance_id)
    {
        $attendance = Attendance::find($attendance_id);
        $date = $attendance->date;

        $application = AmendmentApplication::create([
            'attendance_id' => $attendance_id,
            'approval_status_id' => ApplicationStatus::APPROVED->value,
            'date' => $date,
            'comment' => $request->input('comment'),
            'clock_in' => $request->input('clock_in') 
                ? Carbon::parse($date->format('Y-m-d') . ' ' . $request->input('clock_in')) 
                : null,
            'clock_out' => $request->input('clock_out') 
                ? Carbon::parse($date->format('Y-m-d') . ' ' . $request->input('clock_out')) 
                : null,
        ]);

        foreach ($request->input('break_start', []) as $index => $start) {
            $breakEnds = $request->input('break_end', []);
            $end = $breakEnds[$index] ?? null;
            if ($start && $end) {
                AmendmentApplicationBreak::create([
                    'amendment_application_id' => $application->id,
                    'break_start' => Carbon::parse($date->format('Y-m-d') . ' ' . $start),
                    'break_end' => Carbon::parse($date->format('Y-m-d') . ' ' . $end),
                ]);
            }
        }

        //修正申請内容を勤怠レコード、休憩レコードへ反映
        app(AmendmentApplicationProcessor::class)->applyToAttendance($application);

        return redirect("/admin/attendance/$attendance_id");
    }
}
