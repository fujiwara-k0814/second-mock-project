@extends('layouts/app')

@if (Auth::guard('web')->check())
    @section('title', '申請一覧画面')
@else
    @section('title', '申請一覧画面(管理者)')
@endif

@section('css')
<link rel="stylesheet" href="{{ asset('css/shared/application-index.css') }}">
@endsection

@section('content')
<div class="application__content">
    <h1 class="application__title">申請一覧</h1>
    <div class="approval-tab__content">
        <a href="{{ Auth::guard('web')->check() 
            ? '/stamp_correction_request/list?tab=pending' 
            : '/admin/stamp_correction_request/list?tab=pending' }}" 
            class="pending {{ request('tab') === 'approved' ? '' : 'active' }}">承認待ち</a>
        <a href="{{ Auth::guard('web')->check() 
            ? '/stamp_correction_request/list?tab=approved' 
            : '/admin/stamp_correction_request/list?tab=approved' }}" 
            class="approved {{ request('tab') === 'approved' ? 'active' : '' }}">承認済み</a>
    </div>
    <table class="application-table">
        <tr>
            <th>状態</th>
            <th>名前</th>
            <th>対象日</th>
            <th>申請理由</th>
            <th>申請日</th>
            <th>詳細</th>
        </tr>
        @foreach ($attendances as $attendance)
            @foreach ($attendance->amendmentApplications as $application)
                <tr>
                    <td>{{ $application->approvalStatus->name }}</td>
                    <td>{{ $attendance->user->name }}</td>
                    <td>{{ $attendance->date->isoFormat('MM/DD') }}</td>
                    <td>{{ $application->comment }}</td>
                    <td>{{ $application->created_at->isoFormat('MM/DD') }}</td>
                    <td>
                        <a href="{{ Auth::guard('web')->check() 
                            ? "/attendance/detail/$application->attendance_id" 
                            : "/admin/stamp_correction_request/approve/$application->id" }}" class="application-detail__link">詳細</a>
                    </td>
                </tr>
            @endforeach
        @endforeach
    </table>
</div>
@endsection