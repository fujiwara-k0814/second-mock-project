<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use App\Models\AmendmentApplication;
use App\Models\AmendmentApplicationBreak;
use App\Enums\ApplicationStatus;

class AdminAttendanceController extends Controller
{
    public function index($date = null)
    {
        $targetDate = $date ? Carbon::create($date) : Carbon::now();
        $attendances = Attendance::with('user', 'amendmentApplications')->whereDate('date', $targetDate)->orderBy('created_at')->get();
        $prev = $targetDate->copy()->subDay();
        $next = $targetDate->copy()->addDay();

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

        return view('admin.attendance-index', compact('attendances', 'targetDate', 'prev', 'next'));
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

    public function Correction(Request $request, $attendance_id)
    {
        $attendance = Attendance::find($attendance_id);
        $date = $attendance->date;
        $amendmentApplicationBreaks = [];

        $application['attendance_id'] = $attendance_id;
        $application['approval_status_id'] = ApplicationStatus::APPROVED;
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
                $amendmentApplicationBreaks[] = AmendmentApplicationBreak::create($break);
            }
        }

        //レコード更新
        $attendance->clock_in = $amendmentApplication->clock_in;
        $attendance->clock_out = $amendmentApplication->clock_out;
        $attendance->comment = $amendmentApplication->comment;
        $attendance->save();

        $attendance->attendanceBreaks()->delete();
        foreach ($amendmentApplicationBreaks as $amendmentApplicationBreak) {
            AttendanceBreak::create([
                'attendance_id' => $attendance->id,
                'break_start' => $amendmentApplicationBreak->break_start,
                'break_end' => $amendmentApplicationBreak->break_end,
            ]);
        }

        return redirect("/admin/attendance/$attendance_id");
    }
}
