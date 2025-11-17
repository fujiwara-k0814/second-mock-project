@extends('layouts/app')

@section('title', '勤怠一覧画面(管理者)')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance-index.css') }}">
@endsection

@section('content')
<div class="admin-attendance__content">
    <h1 class="admin-attendance__title">
        {{ $targetDate->locale('ja')->isoFormat('YYYY年M月D日') }}の勤怠
    </h1>
    <nav class="admin-attendance-paginate">
        <a href="/admin/attendance/list/{{ $prev->year }}/{{ $prev->month }}/{{ $prev->day }}" 
            class="admin-attendance-paginate__link">
            <span class="admin-attendance-paginate__link arrow">← </span>前日
        </a>
        <p class="current-date">{{ $targetDate->isoFormat('YYYY/MM/DD') }}</p>
        <a href="/admin/attendance/list/{{ $next->year }}/{{ $next->month }}/{{ $next->day }}" 
            class="admin-attendance-paginate__link">
            翌日<span class="admin-attendance-paginate__link arrow"> →</span>
        </a>
    </nav>
    <table class="admin-attendance-table">
        <tr class="header-row">
            <th class="table-header">名前</th>
            <th class="table-header">出勤</th>
            <th class="table-header">退勤</th>
            <th class="table-header">休憩</th>
            <th class="table-header">合計</th>
            <th class="table-header table-header-detail">詳細</th>
        </tr>
        @foreach ($attendances as $attendance)
            <tr class="data-row">
                <td class="table-data">
                    {{ $attendance->user->name }}
                </td>
                <td class="table-data">
                    {{ $attendance->clock_in ? $attendance->clock_in->format('H:i') : '' }}
                </td>
                <td class="table-data">
                    {{ $attendance->clock_out ? $attendance->clock_out->format('H:i') : '' }}
                </td>
                <td class="table-data">
                    {{ 
                        $attendance->total_break_seconds 
                            ? sprintf(
                                '%d:%02d', 
                                floor($attendance->total_break_seconds / 3600), 
                                ($attendance->total_break_seconds % 3600) / 60
                            ) 
                            : '' 
                    }}
                </td>
                <td class="table-data">
                    {{ 
                        $attendance->actual_work_seconds 
                            ? sprintf(
                                '%d:%02d', 
                                floor($attendance->actual_work_seconds / 3600), 
                                ($attendance->actual_work_seconds % 3600) / 60
                            ) 
                            : '' 
                    }}
                </td>
                <td class="table-data table-data-detail">
                    <a href="/admin/attendance/{{ $attendance->id }}" 
                        class="admin-attendance-detail__link">詳細</a>
                </td>
            </tr>
        @endforeach
    </table>
</div>
@endsection