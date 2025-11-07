@extends('layouts/app')

@section('title', '勤怠一覧画面')
    
@section('css')
<link rel="stylesheet" href="{{ asset('css/user/attendance-index.css') }}">    
@endsection

@section('content')
<div class="user-attendance__content">
    <h1 class="user-attendance__title">勤怠一覧</h1>
    <nav class="attendance-paginate">
        <a href="/attendance/list/{{ $prev->year }}/{{ $prev->month }}" class="attendance-paginate__link">
            ← 前月
        </a>
        <p class="cuttent-month">{{ $targetDate->isoFormat('YYYY/MM') }}</p>
        <a href="/attendance/list/{{ $next->year }}/{{ $next->month }}" class="attendance-paginate__link">
            → 翌月
        </a>
    </nav>
    <table class="attendance-table">
        <tr>
            <th>日付</th>
            <th>出勤</th>
            <th>退勤</th>
            <th>休憩</th>
            <th>合計</th>
            <th>詳細</th>
        </tr>
        @foreach ($attendances as $attendance)
            <tr>
                <td>{{ $attendance->date->locale('ja')->isoFormat('MM/DD(ddd)') }}</td>
                <td>{{ $attendance->clock_in ? $attendance->clock_in->format('H:i') : '' }}</td>
                <td>{{ $attendance->clock_out ? $attendance->clock_out->format('H:i') : '' }}</td>
                <td>{{ $attendance->total_break_seconds ? sprintf('%02d:%02d', floor($attendance->total_break_seconds / 3600), ($attendance->total_break_seconds % 3600) / 60) : '' }}</td>
                <td>{{ $attendance->actual_work_seconds ? sprintf('%02d:%02d', floor($attendance->actual_work_seconds / 3600), ($attendance->actual_work_seconds % 3600) / 60) : '' }}</td>
                <td>
                    <a href="/attendance/detail/{{ $attendance->id }}" class="attendance-detail__link">詳細</a>
                </td>
            </tr>
        @endforeach
    </table>
</div>
@endsection