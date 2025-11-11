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

class UserAttendanceController extends Controller
{
    public function create()
    {
        /** @var \App\Models\User $user */
        $user = Auth::guard('web')->user();
        $attendance = $user->attendances()->with('attendanceBreaks')->whereDate('date', Carbon::now())->first();

        if ($attendance?->attendanceBreaks?->isNotEmpty()) {
            $break = $attendance->latestAttendanceBreak;
        } else {
            $break = null;
        }
        
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
        
        return view('user.time-stamp', compact('status'));
    }

    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::guard('web')->user();
        $attendance = $user->attendances()->whereDate('date', Carbon::now())->first();
        $status = AttendanceStatus::from($request->input('status'));

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
        $targetDate = Carbon::createFromDate(
            $year ?? Carbon::now()->year,   //'now()->**'省略時に現在年月を表示
            $month ?? Carbon::now()->month, 
            1,   //1日を起点とさせる為'1'を指定
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
        
        //総勤務、総休憩、総稼働プロパティ追加(終了時間が無いなどの場合は'null')
        $attendances->each(function ($attendance) {
            $attendance->total_work_seconds = (
                $attendance->clock_in && $attendance->clock_out
            )
                ? $attendance->clock_out->diffInSeconds($attendance->clock_in)
                : null;

            $attendance->total_break_seconds = $attendance->attendanceBreaks
                ->sum(function ($break) {
                    return ($break->break_start && $break->break_end)
                        ? $break->break_end->diffInSeconds($break->break_start)
                        : null;
                });

            $attendance->actual_work_seconds = (
                $attendance->total_work_seconds &&$attendance->total_break_seconds
            )
                ? max(0, $attendance->total_work_seconds - $attendance->total_break_seconds)
                : null;
        });

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
        $date = $displayAttendance->date;
        $statusCode = $displayAttendance->latestAmendmentApplication?->approvalStatus->code;
        
        if ($statusCode === 'pending') {
            $displayAttendance = $displayAttendance->latestAmendmentApplication;
            $breaks = AmendmentApplicationBreak::where('amendment_application_id', $displayAttendance->id)->orderBy('break_start')->get();
            
        }
        
        return view('shared.attendance-detail', compact(
            'displayAttendance', 
            'attendanceId', 
            'breaks', 
            'user', 
            'date', 
            'statusCode', 
        ));
    }

    public function application(StampCorrectionRequest $request, $attendance_id)
    {
        $attendance = Attendance::find($attendance_id);
        $date = $attendance->date;

        $application['attendance_id'] = $attendance_id;
        $application['approval_status_id'] = ApplicationStatus::PENDING;
        $application['comment'] = $request->input('comment');
        if ($request->input('clock_in')) {
            $application['clock_in'] = Carbon::parse(
                $date->format('Y-m-d') . ' ' . $request->input('clock_in')
            );
        } else {
            $application['clock_in'] = null;
        }
        if ($request->input('clock_out')) {
            $application['clock_out'] = Carbon::parse(
                $date->format('Y-m-d') . ' ' . $request->input('clock_out')
            );
        } else {
            $application['clock_out'] = null;
        }
        $amendmentApplication = AmendmentApplication::create($application);
        
        foreach ($request->input('break_start', []) as $index => $start) {
            $breakEnds = $request->input('break_end', []);
            $end = $breakEnds[$index] ?? null;
            if ($start && $end) {
                $break['break_start'] = Carbon::parse(
                    $date->format('Y-m-d') . ' ' . $start
                );
                $break['break_end'] = Carbon::parse(
                    $date->format('Y-m-d') . ' ' . $end
                );
                $break['amendment_application_id'] = $amendmentApplication->id;
                AmendmentApplicationBreak::create($break);
            }
        }

        return redirect("/attendance/detail/$attendance_id");
    }
}
