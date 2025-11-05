@extends('layouts/app')

@if (Auth::guard('web'))
    @section('title', '勤怠詳細')
@else
    @section('title', '勤怠詳細(管理者)')
@endif

@section('css')
<link rel="stylesheet" href="{{ asset('css/shared/attendance-detail') }}">
@endsection

@section('content')
<div class="detail__content">
    <h1 class="detail__title">勤怠詳細</h1>
    <form action="" method="post" class="detail-form">
        <table class="detail-table">
            <tr>
                <th>名前</th>
                <td>{{ $user->name }}</td>
            </tr>
            <tr>
                <th>日付</th>
                <td>
                    <span>{{ $date->locale('ja')->isoFormat('YYYY年') }}</span>
                    <span>{{ $date->locale('ja')->isoFormat('M月d日') }}</span>
                </td>
            </tr>
            <tr>
                <th><label for="clock">出勤・退勤</label></th>
                <td>
                    <input type="time" name="clock_in" id="clock" 
                        value="{{ old('clock_in') 
                            ?? optional($attendance)?->clock_in?->Format('H:i') }}">
                    <span>～</span>
                    <input type="time" name="clock_out" id="clock" 
                        value="{{ old('clock_out') 
                            ?? optional($attendance)?->clock_out?->Format('H:i') }}">
                </td>
            </tr>
            @forelse ($attendance->attendanceBreaks as $index => $break)
                <tr class="break-row">
                    <th>休憩{{ $index === 0 ? '' : $index + 1 }}</th>
                    <td>
                        <input type="time" name="break_start[{{ $index }}]" value="{{ old("break_start.$index") ?? $break->break_start?->format('H:i') }}">
                        <span>～</span>
                        <input type="time" name="break_end[{{ $index }}]" value="{{ old("break_end.$index") ?? $break->break_end?->format('H:i') }}">
                    </td>
                </tr>
            @empty
                <tr class="break-row">
                    <th>休憩</th>
                    <td>
                        <input type="time" name="break_start[0]">
                        <span>～</span>
                        <input type="time" name="break_end[0]">
                    </td>
                </tr>
            @endforelse
            <tr>
                <th><label for="comment">備考</label></th>
                <td>
                    <textarea name="comment" id="comment">
                        {{ old('comment') ?? optional($attendance)->comment }}
                    </textarea>
                </td>
            </tr>
        </table>
        <button type="submit" class="detail-form__button">修正</button>
    </form>
</div>
<script src="{{ asset('js/breaks.js') }}"></script>
@endsection