<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'clock_in',
        'clock_out',
        'comment',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'date',
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    //AttendanceBreak
    //全件取得
    public function attendanceBreaks()
    {
        return $this->hasMany(AttendanceBreak::class);
    }
    //最新取得
    public function latestAttendanceBreak()
    {
        return $this->hasOne(AttendanceBreak::class)->latestOfMany();
    }

    //AmendmentApplication
    //全件取得
    public function amendmentApplications()
    {
        return $this->hasMany(AmendmentApplication::class);
    }
    //最新取得
    public function latestAmendmentApplication()
    {
        return $this->hasOne(AmendmentApplication::class)->latestOfMany();
    }
}
