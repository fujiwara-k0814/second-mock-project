<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AmendmentApplicationBreak extends Model
{
    use HasFactory;

    protected $fillable = [
        'amendment_application_id',
        'break_start',
        'break_end',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'break_start' => 'datetime',
        'break_end' => 'datetime',
    ];

    public function amendmentApplication()
    {
        return $this->belongsTo(AmendmentApplication::class);
    }
}
