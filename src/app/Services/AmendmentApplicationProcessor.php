<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\AmendmentApplication;
use App\Models\AttendanceBreak;
use Illuminate\Support\Facades\DB;

class AmendmentApplicationProcessor
{
    public function applyToAttendance(AmendmentApplication $application): void
    {
        $attendance = Attendance::with('attendanceBreaks')
            ->find($application->attendance_id);

        $application->load('amendmentApplicationBreaks');

        DB::transaction(function () use ($attendance, $application) {
            //勤怠レコード更新
            $attendance->update([
                'clock_in' => $application->clock_in,
                'clock_out' => $application->clock_out,
                'comment' => $application->comment,
            ]);

            //休憩レコード削除 → 再生成
            $attendance->attendanceBreaks()->delete();

            foreach ($application->amendmentApplicationBreaks as $break) {
                AttendanceBreak::create([
                    'attendance_id' => $attendance->id,
                    'break_start' => $break->break_start,
                    'break_end' => $break->break_end,
                ]);
            }

            $application->update([
                'approval_status_id' => \App\Enums\ApplicationStatus::APPROVED->value,
            ]);
        });
    }
}