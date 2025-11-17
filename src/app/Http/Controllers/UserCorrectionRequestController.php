<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\AmendmentApplication;
use App\Enums\ApplicationStatus;

class UserCorrectionRequestController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::guard('web')->user();
        $attendanceIds = $user->attendances()->pluck('id');

        if (request('tab') === 'approved') {
            $applications = AmendmentApplication::with(
                'attendance.user',
                'approvalStatus'
            )
            ->where('approval_status_id', ApplicationStatus::APPROVED->value)
            ->whereIn('attendance_id', $attendanceIds)
            ->orderBy('date')
            ->get();
        } else {
            $applications = AmendmentApplication::with(
                'attendance.user',
                'approvalStatus'
            )
            ->where('approval_status_id', ApplicationStatus::PENDING->value)
            ->whereIn('attendance_id', $attendanceIds)
            ->orderBy('date')
            ->get();
        }

        return view('shared.application-index', compact('applications'));
    }
}
