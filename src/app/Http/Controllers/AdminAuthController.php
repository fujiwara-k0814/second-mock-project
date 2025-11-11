<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\LoginRequest;

class AdminAuthController extends Controller
{
    public function show()
    {
        return view('shared.auth.login');
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');
        
        if (Auth::guard('admin')->attempt($credentials)) {
            return redirect('/admin/attendance/list');
        }

        return back()->withErrors([
            'email' => 'ログイン情報が登録されていません',
        ])->withInput();
    }


    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/admin/login');
    }
}
