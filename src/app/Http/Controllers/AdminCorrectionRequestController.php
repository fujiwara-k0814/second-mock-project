<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\AmendmentApplication;
use App\Models\AmendmentApplicationBreak;
use App\Enums\ApplicationStatus;
use App\Models\AttendanceBreak;

class AdminCorrectionRequestController extends Controller
{
    public function index()
    {
        if (request('tab') === 'approved') {
            $attendances = Attendance::with('user', 'amendmentApplications.approvalStatus')
                ->whereHas('amendmentApplications.approvalStatus', function ($q) {
                    $q->whereNotIn('code', ['pending']);
                })
                ->orderBy('date')
                ->get();
        } else {
            $attendances = Attendance::with('user', 'amendmentApplications.approvalStatus')
                ->whereHas('amendmentApplications.approvalStatus', function ($q) {
                    $q->whereNotIn('code', ['approved']);
                })
                ->orderBy('date')
                ->get();
        }

        return view('shared.application-index', compact('attendances'));
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
            ->orderBy('break_start')->get();
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
        $amendment = AmendmentApplication::with('amendmentApplicationBreaks')
            ->find($amendment_id);
        $attendance = Attendance::with('attendanceBreaks')
            ->find($amendment->attendance_id);
        $attendance->clock_in = $amendment->clock_in;
        $attendance->clock_out = $amendment->clock_out;
        $attendance->comment = $amendment->comment;
        $attendance->save();

        $attendance->attendanceBreaks()->delete();
        foreach ($amendment->amendmentApplicationBreaks as $break) {
            AttendanceBreak::create([
                'attendance_id' => $attendance->id,
                'break_start' => $break->break_start,
                'break_end' => $break->break_end,
            ]);
        }

        $amendment->approval_status_id = ApplicationStatus::APPROVED;
        $amendment->save();

        return redirect("admin/stamp_correction_request/approve/$amendment->id");
    }
}
