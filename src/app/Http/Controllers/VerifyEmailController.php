<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

class VerifyEmailController extends Controller
{
    public function notice()
    {
        return view('user.auth.verify-email');
    }

    public function verify(EmailVerificationRequest $request)
    {
        $request->fulfill();

        return redirect('/attendance');
    }

    public function send(Request $request)
    {
        $request->user()->sendEmailVerificationNotification();

        return redirect('/email/verify');
    }
}
