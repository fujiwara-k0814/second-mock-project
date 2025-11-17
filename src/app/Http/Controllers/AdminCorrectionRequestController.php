<?php

namespace App\Http\Controllers;

use App\Models\AmendmentApplication;
use App\Models\AmendmentApplicationBreak;
use App\Enums\ApplicationStatus;
use App\Services\AmendmentApplicationProcessor;

class AdminCorrectionRequestController extends Controller
{
    public function index()
    {
        if (request('tab') === 'approved') {
            $applications = AmendmentApplication::with(
                'attendance.user', 
                'approvalStatus'
            )
            ->where('approval_status_id', ApplicationStatus::APPROVED->value)
            ->orderBy('date')
            ->get();

        } else {
            $applications = AmendmentApplication::with(
                'attendance.user', 
                'approvalStatus'
            )
            ->where('approval_status_id', ApplicationStatus::PENDING->value)
            ->orderBy('date')
            ->get();
        }

        return view('shared.application-index', compact('applications'));
    }

    public function edit($application_id)
    {
        $amendment = AmendmentApplication::with([
            'attendance.user',
            'approvalStatus',
            'amendmentApplicationBreaks'
        ])->find($application_id);
        
        $breaks = AmendmentApplicationBreak::where(
            'amendment_application_id', $application_id
            )
            ->orderBy('break_start')
            ->get();
        
        //空のレコード追加
        if ($breaks) {
            $breaks->push(new AmendmentApplicationBreak([
                'break_start' => null,
                'break_end' => null,
            ]));
        }

        return view('admin.amendment-application', compact(
            'amendment',
            'breaks',
        ));
    }

    public function update($amendment_id)
    {
        $application = AmendmentApplication::find($amendment_id);

        app(AmendmentApplicationProcessor::class)->applyToAttendance($application);

        return redirect("admin/stamp_correction_request/approve/$amendment_id");
    }
}
