<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AmendmentApplication;

class UserCorrectionRequestController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::guard('web')->user();

        if (request('tab') === 'approved') {
            $attendances = $user->attendances()
                ->with('amendmentApplications.approvalStatus')
                ->whereHas('amendmentApplications.approvalStatus', function ($q) {
                    $q->whereNotIn('code', ['pending']);
                })
                ->get();
        } else {
            $attendances = $user->attendances()
                ->with('amendmentApplications.approvalStatus')
                ->whereHas('amendmentApplications.approvalStatus', function ($q) {
                    $q->whereNotIn('code', ['approved']);
                })
                ->get();
        }

        return view('shared.application-index', compact('attendances'));
    }
}
