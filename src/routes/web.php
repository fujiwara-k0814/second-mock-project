<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserAttendanceController;
use App\Http\Controllers\UserCorrectionRequestController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminAttendanceController;
use App\Http\Controllers\AdminStaffAttendanceController;
use App\Http\Controllers\AdminCorrectionRequestController;
use App\Http\Controllers\StaffAttendanceExportController;
use App\Http\Controllers\VerifyEmailController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//ユーザー：勤怠関連
Route::middleware('auth:web', 'verified')->prefix('attendance')->group(function () {
    Route::get('/', [UserAttendanceController::class, 'create']);
    Route::post('/', [UserAttendanceController::class, 'store']);
    Route::get('/list/{year?}/{month?}', [UserAttendanceController::class, 'index']);
    Route::get('/detail/{id}', [UserAttendanceController::class, 'edit']);
    Route::post('/detail/{id}', [UserAttendanceController::class, 'application']);
});
//ユーザー：申請関連
Route::middleware(['auth:web', 'verified'])->group(function () {
    Route::get('/stamp_correction_request/list', [UserCorrectionRequestController::class, 'index']);
});
//ユーザー：メール認証(システム制約でname付与)
Route::get('/email/verify', [VerifyEmailController::class, 'notice'])
    ->middleware('auth')
    ->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, 'verify'])
    ->middleware(['auth', 'signed'])
    ->name('verification.verify');

Route::post('/email/verification_notification', [VerifyEmailController::class, 'send'])
    ->middleware(['auth', 'throttle:10,1'])
    ->name('verification.send');



//管理者：勤怠、スタッフ関連
Route::prefix('admin')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'show']);
    Route::post('/login', [AdminAuthController::class, 'login']);
    Route::middleware('auth:admin')->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout']);
        Route::get('/attendance/list/{year?}/{month?}/{day?}', [AdminAttendanceController::class, 'index']);
        Route::get('/attendance/{id}', [AdminAttendanceController::class, 'edit']);
        Route::post('/attendance/{id}', [AdminAttendanceController::class, 'correction']);
        Route::get('/staff/list', [AdminStaffAttendanceController::class, 'index']);
        Route::get('/attendance/staff/{id}/{year?}/{month?}', [AdminStaffAttendanceController::class, 'show']);
        Route::get('/attendance/staff/{id}/{year?}/{month?}/export', [StaffAttendanceExportController::class, 'export']);
    });
});
//管理者：申請関連
Route::middleware(['auth:admin'])->prefix('admin/stamp_correction_request')->group(function () {
    Route::get('list', [AdminCorrectionRequestController::class, 'index']);
    Route::get('approve/{attendance_correct_request_id}', [AdminCorrectionRequestController::class, 'edit']);
    Route::post('approve/{attendance_correct_request_id}', [AdminCorrectionRequestController::class, 'update']);
});

