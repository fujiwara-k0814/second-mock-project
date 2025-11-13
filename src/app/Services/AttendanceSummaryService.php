<?php

namespace App\Services;

use Illuminate\Support\Collection;

class AttendanceSummaryService
{
    public function summarize(Collection $attendances): Collection
    {
        return $attendances->each(function ($attendance) {
            //総勤務時間
            $attendance->total_work_seconds = (
                $attendance->clock_in && $attendance->clock_out
            ) ? $attendance->clock_out->diffInSeconds($attendance->clock_in) : null;
            
            //総休憩時間
            $attendance->total_break_seconds = $attendance->attendanceBreaks->sum(function ($break) {
                return ($break->break_start && $break->break_end)
                    ? $break->break_end->diffInSeconds($break->break_start)
                    : 0;
            });

            //総稼働時間
            $attendance->actual_work_seconds = (
                $attendance->total_work_seconds 
                && $attendance->total_break_seconds 
            ) ? max(0, $attendance->total_work_seconds - $attendance->total_break_seconds) : null;
        });
    }
}