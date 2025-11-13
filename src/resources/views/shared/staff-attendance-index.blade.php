@extends('layouts/app')


@if (Auth::guard('web')->check())
    @section('title', '勤怠一覧画面')
@else
    @section('title', 'スタッフ別勤怠一覧画面(管理者)')
@endif
    
@section('css')
<link rel="stylesheet" href="{{ asset('css/shared/staff-attendance-index.css') }}">    
@endsection

@section('content')
<div class="attendance__content">
    <h1 class="attendance__title">
        {{ Auth::guard('web')->check() ? '勤怠一覧' : $user->name . 'さんの勤怠' }}
    </h1>
    <nav class="attendance-paginate">
        <a href="{{ Auth::guard('web')->check() 
            ? "/attendance/list/$prev->year/$prev->month" 
            : "/admin/attendance/staff/$user->id/$prev->year/$prev->month" }}" class="attendance-paginate__link">
            <span class="attendance-paginate__link arrow">← </span>前月
        </a>
        <p class="current-month">{{ $targetDate->isoFormat('YYYY/MM') }}</p>
        <a href="{{ Auth::guard('web')->check() 
            ? "/attendance/list/$next->year/$next->month" 
            : "/admin/attendance/staff/$user->id/$next->year/$next->month" }}" class="attendance-paginate__link">
            翌月<span class="attendance-paginate__link arrow"> →</span>
        </a>
    </nav>
    <table class="attendance-table">
        <tr class="header-row">
            <th class="table-header table-header-date">日付</th>
            <th class="table-header">出勤</th>
            <th class="table-header">退勤</th>
            <th class="table-header">休憩</th>
            <th class="table-header">合計</th>
            <th class="table-header table-header-detail">詳細</th>
        </tr>
        @foreach ($attendances as $attendance)
            <tr class="data-row">
                <td class="table-data table-data-date">{{ $attendance->date->locale('ja')->isoFormat('MM/DD(ddd)') }}</td>
                <td class="table-data">{{ $attendance->clock_in ? $attendance->clock_in->format('H:i') : '' }}</td>
                <td class="table-data">{{ $attendance->clock_out ? $attendance->clock_out->format('H:i') : '' }}</td>
                <td class="table-data">{{ $attendance->total_break_seconds ? sprintf('%02d:%02d', floor($attendance->total_break_seconds / 3600), ($attendance->total_break_seconds % 3600) / 60) : '' }}</td>
                <td class="table-data">{{ $attendance->actual_work_seconds ? sprintf('%02d:%02d', floor($attendance->actual_work_seconds / 3600), ($attendance->actual_work_seconds % 3600) / 60) : '' }}</td>
                <td class="table-data table-data-detail">
                    <a href="{{ Auth::guard('web')->check() 
                        ? "/attendance/detail/$attendance->id" 
                        : "/admin/attendance/$attendance->id" }}" class="attendance-detail__link">詳細</a>
                </td>
            </tr>
        @endforeach
    </table>
    @if (Auth::guard('admin')->check())
        <form action="/admin/attendance/staff/{{ $user->id }}/{{ $targetDate->year }}/{{ $targetDate->month }}/export" method="get" class="export-form">
            <button type="submit" class="export__button">CSV出力</button>
        </form>
    @endif
</div>
@endsection