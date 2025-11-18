<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use App\Models\AmendmentApplication;
use App\Models\AmendmentApplicationBreak;
use App\Enums\ApplicationStatus;
use App\Http\Requests\StampCorrectionRequest;
use App\Services\AttendanceSummaryService;

class UserAttendanceController extends Controller
{
    public function create()
    {
        /** @var \App\Models\User $user */
        $user = Auth::guard('web')->user();
        $attendance = $user->attendances()
            ->with('attendanceBreaks')
            ->whereDate('date', Carbon::now())
            ->first();
        
        $dateTime = Carbon::now();

        if ($attendance?->attendanceBreaks?->isNotEmpty()) {
            $break = $attendance->latestAttendanceBreak;
        } else {
            $break = null;
        }
        
        //勤怠状態でEnumステータス取得
        if ($attendance?->clock_in) {
            if ($attendance->clock_out) {
                $status = AttendanceStatus::FINISHED;
            } else {
                if ($break && !$break->break_end) {
                    $status = AttendanceStatus::ON_BREAK;
                } else {
                    $status = AttendanceStatus::WORKING;
                }
            }
        } else {
            $status = AttendanceStatus::OFF;
        }
        
        return view('user.time-stamp', compact('status', 'dateTime'));
    }

    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::guard('web')->user();
        $attendance = $user->attendances()->whereDate('date', Carbon::now())->first();
        //Enum型に変換して取得
        $status = AttendanceStatus::from($request->input('status'));

        //勤務状況で処理分岐
        switch ($status) {
            case AttendanceStatus::OFF:
                if ($attendance) {
                    $attendance->update([
                        'clock_in' => Carbon::now(),
                    ]);
                } else {
                    $user->attendances()->create([
                        'date' => Carbon::now(),
                        'clock_in' => Carbon::now(),
                    ]);
                }
                break;

            case AttendanceStatus::WORKING:
                if ($request->has('finish')) {
                    $attendance->update([
                        'clock_out' => Carbon::now(),
                    ]);
                } else {
                    $attendance->attendanceBreaks()->create([
                        'break_start' => Carbon::now(),
                    ]);
                }
                break;

            case AttendanceStatus::ON_BREAK:
                $attendance->latestAttendanceBreak
                    ->update(['break_end' => Carbon::now()]);
                break;

            default:
                break;
        }

        return redirect('/attendance');
    }

    //ルート引数の初期値を'null'に指定
    public function index($year = null, $month = null) 
    {
        /** @var \App\Models\User $user */
        $user = Auth::guard('web')->user();

        //'now()->**'省略時に現在年月を表示
        //1日を起点とさせる為'1'を指定
        $targetDate = Carbon::createFromDate(
            $year ?? Carbon::now()->year,
            $month ?? Carbon::now()->month, 
            1,
        )
        ->startOfMonth();
        $prev = $targetDate->copy()->subMonth();
        $next = $targetDate->copy()->addMonth();

        $attendances = $user->attendances()
            ->with('attendanceBreaks')
            ->whereBetween('date', [
                $targetDate->copy()->startOfMonth(),
                $targetDate->copy()->endOfMonth(),
            ])
            ->orderBy('date')
            ->get();

        //プロパティ追加
        //総勤務 → 'total_work_seconds' 総休憩 → 'total_break_seconds' 総稼働 → 'actual_work_seconds'
        app(AttendanceSummaryService::class)->summarize($attendances);

        return view('shared.staff-attendance-index', compact(
            'attendances', 
            'targetDate',
            'prev',
            'next',
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

    public function application(StampCorrectionRequest $request, $attendance_id)
    {
        $attendance = Attendance::find($attendance_id);
        $date = $attendance->date;

        $application = AmendmentApplication::create([
            'attendance_id' => $attendance_id,
            'approval_status_id' => ApplicationStatus::PENDING->value,
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

        return redirect("/attendance/detail/$attendance_id");
    }
}
