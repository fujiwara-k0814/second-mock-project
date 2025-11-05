<?php

namespace App\Enums;

enum AttendanceStatus: string
{
    case OFF = 'off';
    case WORKING = 'working';
    case ON_BREAK = 'on_break';
    case FINISHED = 'finished';

    public function label(): string
    {
        return match ($this) {
            self::WORKING => '出勤中',
            self::ON_BREAK => '休憩中',
            self::FINISHED => '退勤済',
            default => '勤務外',
        };
    }
}