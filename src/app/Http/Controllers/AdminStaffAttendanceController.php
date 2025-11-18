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
        //'now()->**'省略時に現在年月を表示
        //1日を起点とさせる為'1'を指定
        $user = User::find($user_id);
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
            'user',
        ));
    }
}
