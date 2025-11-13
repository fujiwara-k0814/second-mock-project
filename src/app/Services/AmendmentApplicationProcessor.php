<?php

namespace App\Services;

use App\Models\AmendmentApplication;
use App\Models\AttendanceBreak;
use Illuminate\Support\Facades\DB;

class AmendmentApplicationProcessor
{
    public function applyToAttendance(AmendmentApplication $application): void
    {
        DB::transaction(function () use ($application) {
            $attendance = $application->attendance;

            $attendance->update([
                'clock_in' => $application->clock_in,
                'clock_out' => $application->clock_out,
                'comment' => $application->comment,
            ]);

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