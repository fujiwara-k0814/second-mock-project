@extends('layouts/app')

@if (Auth::guard('web')->check())
    @section('title', '勤怠詳細画面')
@else
    @section('title', '勤怠詳細画面(管理者)')
@endif

@section('css')
<link rel="stylesheet" href="{{ asset('css/shared/attendance-detail.css') }}">
@endsection

@section('content')
<div class="detail__content">
    <h1 class="detail__title">勤怠詳細</h1>
    <form action="{{ Auth::guard('web')->check() 
        ? "/attendance/detail/$attendanceId" 
        : "/admin/attendance/$attendanceId" }}" 
        method="post" class="detail-form">
        @csrf
        <table class="detail-table">
            <tr>
                <th>名前</th>
                <td>{{ $user->name }}</td>
            </tr>
            <tr>
                <th>日付</th>
                <td>
                    <span>{{ $date->locale('ja')->isoFormat('YYYY年') }}</span>
                    <span>{{ $date->locale('ja')->isoFormat('M月D日') }}</span>
                </td>
            </tr>
            <tr>
                <th><label for="clock">出勤・退勤</label></th>
                <td>
                    <input type="time" name="clock_in" id="clock" class="detail-form__input {{ $statusCode === 'pending' ? "disable" : '' }}" @if($statusCode === 'pending') disabled @endif 
                        value="{{ old('clock_in') 
                            ?? optional($displayAttendance)?->clock_in?->Format('H:i') }}">
                    <span>～</span>
                    <input type="time" name="clock_out" id="clock" class="detail-form__input {{ $statusCode === 'pending' ? "disable" : '' }}" @if($statusCode === 'pending') disabled @endif  
                        value="{{ old('clock_out') 
                            ?? optional($displayAttendance)?->clock_out?->Format('H:i') }}">
                </td>
            </tr>
            @forelse ($breaks as $index => $break)
                <tr class="break-row">
                    <th>
                        {{-- 休憩→休憩2→休憩3を作成するためindex=0除外で以降+1 --}}
                        <label for="break[{{ $index }}]">休憩{{ $index === 0 ? '' : $index + 1 }}</label>
                    </th>
                    <td>
                        <input type="time" name="break_start[{{ $index }}]" id="break[{{ $index }}]" class="detail-form__input {{ $statusCode === 'pending' ? "disable" : '' }}" @if($statusCode === 'pending') disabled @endif value="{{ old("break_start.$index") ?? $break->break_start?->format('H:i') }}">
                        <span>～</span>
                        <input type="time" name="break_end[{{ $index }}]" id="break[{{ $index }}]" class="detail-form__input {{ $statusCode === 'pending' ? "disable" : '' }}" @if($statusCode === 'pending') disabled @endif value="{{ old("break_end.$index") ?? $break->break_end?->format('H:i') }}">
                    </td>
                </tr>
            @empty
                <tr class="break-row">
                    <th>
                        <label for="break[0]">休憩</label>
                    </th>
                    <td>
                        <input type="time" name="break_start[0]" id="break[0]" class="detail-form__input {{ $statusCode === 'pending' ? "disable" : '' }}" @if($statusCode === 'pending') disabled @endif>
                        <span>～</span>
                        <input type="time" name="break_end[0]" id="break[0]" class="detail-form__input {{ $statusCode === 'pending' ? "disable" : '' }}" @if($statusCode === 'pending') disabled @endif>
                    </td>
                </tr>
            @endforelse
            <tr class="comment-row">
                <th><label for="comment">備考</label></th>
                <td>
                    <textarea name="comment" id="comment" class="detail-form__input detail-form__comment {{ $statusCode === 'pending' ? "disable" : '' }}" @if($statusCode === 'pending') disabled @endif>
                        {{ old('comment') ?? optional($displayAttendance)->comment }}
                    </textarea>
                </td>
            </tr>
        </table>
        @if ($statusCode === 'pending')
            <p class="pending-message">*承認待ちのため修正はできません。</p>
        @else
            <button type="submit" class="detail-form__button">修正</button>
        @endif
    </form>
</div>
<script>
    const statusCode = @json($statusCode);
</script>
<script src="{{ asset('js/breaks.js') }}"></script>
@endsection