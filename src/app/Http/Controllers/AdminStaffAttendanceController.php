<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Carbon;
use App\Services\AttendanceSummaryService;

class AdminStaffAttendanceController extends Controller
{
    public function index()
    {
        $users = User::all();

        return view('admin.staff-index', compact('users'));
    }

    //ルート引数の初期値を'null'に指定
    public function show($user_id, $year = null, $month = null)
    {
        $user = User::find($user_id);
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
                $attendance->total_work_seconds && $attendance->total_break_seconds
            )
                ? max(0, $attendance->total_work_seconds - $attendance->total_break_seconds)
                : null;
        });

        return view('shared.staff-attendance-index', compact(
            'attendances',
            'targetDate',
            'prev',
            'next',
            'user',
        ));
    }
}
