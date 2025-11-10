@extends('layouts/app')

@section('title', '勤怠一覧画面(管理者)')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance-index.css') }}">
@endsection

@section('content')
<div class="admin-attendance__content">
    <h1 class="admin-attendance__title">{{ $targetDate->locale('ja')->isoFormat('YYYY年M月D日') }}の勤怠</h1>
    <nav class="attendance-paginate">
        <a href="/admin/attendance/list/{{ $prev->toDateString() }}" class="attendance-paginate__link">
            ← 前日
        </a>
        <p class="cuttent-month">{{ $targetDate->isoFormat('YYYY/MM/DD') }}</p>
        <a href="/admin/attendance/list/{{ $next->format('Y-m-d') }}" class="attendance-paginate__link">
            → 翌日
        </a>
    </nav>
    <table class="attendance-table">
        <tr>
            <th>名前</th>
            <th>出勤</th>
            <th>退勤</th>
            <th>休憩</th>
            <th>合計</th>
            <th>詳細</th>
        </tr>
        @foreach ($attendances as $attendance)
            <tr>
                <td>{{ $attendance->user->name }}</td>
                <td>{{ $attendance->clock_in ? $attendance->clock_in->format('H:i') : '' }}</td>
                <td>{{ $attendance->clock_out ? $attendance->clock_out->format('H:i') : '' }}</td>
                <td>{{ $attendance->total_break_seconds ? sprintf('%02d:%02d', floor($attendance->total_break_seconds / 3600), ($attendance->total_break_seconds % 3600) / 60) : '' }}</td>
                <td>{{ $attendance->actual_work_seconds ? sprintf('%02d:%02d', floor($attendance->actual_work_seconds / 3600), ($attendance->actual_work_seconds % 3600) / 60) : '' }}</td>
                <td>
                    <a href="/admin/attendance/{{ $attendance->id }}" class="attendance-detail__link">詳細</a>
                </td>
            </tr>
        @endforeach
    </table>
</div>
@endsection