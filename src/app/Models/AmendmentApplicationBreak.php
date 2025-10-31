<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AmendmentApplicationBreak extends Model
{
    use HasFactory;

    protected $fillable = [
        'amendment_application_id',
        'break_start_time',
        'break_end_time',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'break_start_time' => 'time',
        'break_end_time' => 'time',
    ];

    public function amendmentApplication()
    {
        return $this->belongsTo(AmendmentApplication::class);
    }
}
