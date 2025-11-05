<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAttendanceController extends Controller
{
    public function index()
    {
        $admin = Auth::guard('admin')->user();

        return view('admin.attendance-index', compact('admin'));
    }
}
