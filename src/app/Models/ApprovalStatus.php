<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
    ];

    public function amendmentApplications()
    {
        return $this->hasMany(AmendmentApplication::class);
    }
}
