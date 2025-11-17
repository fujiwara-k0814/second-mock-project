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
            class="pending {{ request('tab') === 'approved' ? '' : 'active' }}">
            承認待ち
        </a>
        <a href="{{ Auth::guard('web')->check() 
            ? '/stamp_correction_request/list?tab=approved' 
            : '/admin/stamp_correction_request/list?tab=approved' }}" 
            class="approved {{ request('tab') === 'approved' ? 'active' : '' }}">
            承認済み
        </a>
    </div>
    <table class="application-table">
        <tr class="header-row">
            <th class="table-header table-header-status">状態</th>
            <th class="table-header">名前</th>
            <th class="table-header">対象日時</th>
            <th class="table-header">申請理由</th>
            <th class="table-header">申請日時</th>
            <th class="table-header">詳細</th>
        </tr>
        @foreach ($applications as $application)
            <tr class="data-row">
                <td class="table-data table-data-status">
                    {{ $application->approvalStatus->name }}
                </td>
                <td class="table-data">
                    {{ $application->attendance->user->name }}
                </td>
                <td class="table-data table-data-date">
                    {{ $application->date->isoFormat('YYYY/MM/DD') }}
                </td>
                <td class="table-data">
                    {{ $application->comment }}
                </td>
                <td class="table-data table-data-application">
                    {{ $application->created_at->isoFormat('YYYY/MM/DD') }}
                </td>
                <td class="table-data">
                    <a href="{{ Auth::guard('web')->check() 
                        ? "/attendance/detail/$application->attendance_id" 
                        : "/admin/stamp_correction_request/approve/$application->id" }}" class="application-detail__link">詳細</a>
                </td>
            </tr>
        @endforeach
    </table>
</div>
@endsection