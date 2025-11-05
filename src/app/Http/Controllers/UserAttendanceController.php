<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\AmendmentApplication;
use App\Models\User;

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
            $year ?? now()->year,   //'now()->**'省略時に現在年月を表示
            $month ?? now()->month, 
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
            $attendance->total_work_seconds = ($attendance->clock_in && $attendance->clock_out)
                ? $attendance->clock_out->diffInSeconds($attendance->clock_in)
                : null;

            $attendance->total_break_seconds = $attendance->attendanceBreaks->sum(function ($break) {
                return ($break->break_start && $break->break_end)
                    ? $break->break_end->diffInSeconds($break->break_start)
                    : null;
            });

            $attendance->actual_work_seconds = ($attendance->total_work_seconds &&$attendance->total_break_seconds)
                ? max(0, $attendance->total_work_seconds - $attendance->total_break_seconds)
                : null;
        });

        return view('user.attendance-index', compact(
            'attendances', 
            'targetDate',
            'prev',
            'next'
        ));
    }

    public function edit($attendance_id)
    {
        $attendance = Attendance::with('user', 'attendanceBreaks', 'latestAmendmentApplication')->orderBy('created_at')->find($attendance_id);

        $user = User::find($attendance->user->id);
        $date = $attendance->date;

        if ($attendance->latestAmendmentApplication?->approval_status_id === 2) {
            $attendance = AmendmentApplication::with('amendmentApplicationBreaks')->find($attendance->latestAmendmentApplication->id);
        }
        
        return view('shared.attendance-detail', compact('attendance', 'user', 'date'));
    }
}
