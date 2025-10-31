<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AmendmentApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'approval_status_id',
        'clock_in_time',
        'clock_out_time',
        'comment',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'clock_in_time' => 'time',
        'clock_out_time' => 'time',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function approvalStatus()
    {
        return $this->belongsTo(ApprovalStatus::class);
    }

    public function amendmentApplicationBreaks()
    {
        return $this->hasMany(AmendmentApplicationBreak::class);
    }
}
