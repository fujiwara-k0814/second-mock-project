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
<form action="{{ Auth::guard('web')->check() 
    ? "/attendance/detail/$attendanceId" 
    : "/admin/attendance/$attendanceId" }}" 
    method="post" class="detail-form">
    @csrf
    <h1 class="detail__title">勤怠詳細</h1>
    <table class="detail-table">
        <tr class="name-row">
            <th>名前</th>
            <td><span class="name">{{ $user->name }}</span></td>
        </tr>
        <tr class="date-row">
            <th>日付</th>
            <td>
                <span class="year">
                    {{ $displayAttendance->date->locale('ja')->isoFormat('YYYY年') }}
                </span>
                <span class="date">
                    {{ $displayAttendance->date->locale('ja')->isoFormat('M月D日') }}
                </span>
            </td>
        </tr>
        <tr class="clock-row">
            <th><label for="clock">出勤・退勤</label></th>
            <td>
                <div class="wrapper">
                    <input type="time" name="clock_in" id="clock" 
                        class="detail-form__input {{ $statusCode === 'pending' ? "disable" : '' }}" 
                        @if($statusCode === 'pending') readonly @endif 
                        value="{{ old('clock_in') 
                            ?? optional($displayAttendance)?->clock_in?->Format('H:i') }}">
                    <div class="detail-form__error">
                        @error('clock_in')
                            {{ $message }}
                        @enderror
                    </div>
                </div>
                <span>～</span>
                <div class="wrapper">
                    <input type="time" name="clock_out" id="clock" 
                        class="detail-form__input {{ $statusCode === 'pending' ? "disable" : '' }}" 
                        @if($statusCode === 'pending') readonly @endif  
                        value="{{ old('clock_out') 
                            ?? optional($displayAttendance)?->clock_out?->Format('H:i') }}">
                    <div class="detail-form__error">
                        @error('clock_out')
                            {{ $message }}
                        @enderror
                    </div>
                </div>
            </td>
        </tr>
        @forelse ($breaks as $index => $break)
            <tr class="break-row">
                <th>
                    {{-- 休憩→休憩2→休憩3を作成するためindex=0除外で以降+1 --}}
                    <label for="break[{{ $index }}]">
                        休憩{{ $index === 0 ? '' : $index + 1 }}
                    </label>
                </th>
                <td>
                    <div class="wrapper">
                        <input type="time" name="break_start[{{ $index }}]" id="break[{{ $index }}]" 
                            class="detail-form__input {{ $statusCode === 'pending' ? "disable" : '' }}" 
                            @if($statusCode === 'pending') readonly @endif 
                            value="{{ old("break_start.$index") 
                                ?? $break->break_start?->Format('H:i') }}">
                        <div class="detail-form__error">
                            @error("break_start.$index")
                                {{ $message }}
                            @enderror
                        </div>
                    </div>
                    <span>～</span>
                    <div class="wrapper">
                        <input type="time" name="break_end[{{ $index }}]" id="break[{{ $index }}]" 
                            class="detail-form__input {{ $statusCode === 'pending' ? "disable" : '' }}" 
                            @if($statusCode === 'pending') readonly @endif 
                            value="{{ old("break_end.$index") 
                                ?? $break->break_end?->Format('H:i') }}">
                        <div class="detail-form__error">
                            @error("break_end.$index")
                                {{ $message }}
                            @enderror
                        </div>
                    </div>
                </td>
            </tr>
        @empty
            <tr class="break-row">
                <th>
                    <label for="break[0]">休憩</label>
                </th>
                <td>
                    <div class="wrapper">
                        <input type="time" name="break_start[0]" id="break[0]" 
                            class="detail-form__input {{ $statusCode === 'pending' ? "disable" : '' }}" 
                            @if($statusCode === 'pending') readonly @endif 
                            value="{{ old('break_end.0') }}">
                        <div class="detail-form__error">
                            @error('break_start.0')
                                {{ $message }}
                            @enderror
                        </div>
                    </div>
                    <span>～</span>
                    <div class="wrapper">
                        <input type="time" name="break_end[0]" id="break[0]" 
                            class="detail-form__input {{ $statusCode === 'pending' ? "disable" : '' }}" 
                            @if($statusCode === 'pending') readonly @endif 
                            value="{{ old('break_end.0') }}">
                        <div class="detail-form__error">
                            @error('break_end.0')
                                {{ $message }}
                            @enderror
                        </div>
                    </div>
                </td>
            </tr>
        @endforelse
        <tr class="comment-row">
            <th><label for="comment">備考</label></th>
            <td>
                <div class="wrapper comment-wrapper">
                    <textarea name="comment" id="comment" 
                        class="detail-form__input detail-form__comment 
                            {{ $statusCode === 'pending' ? "disable comment-disable" : '' }}" 
                            @if($statusCode === 'pending') readonly @endif
                            >{{ old('comment') ?? optional($displayAttendance)->comment }}</textarea>
                    <div class="detail-form__error">
                        @error('comment')
                            {{ $message }}
                        @enderror
                    </div>
                </div>
            </td>
        </tr>
    </table>
    <div class="form-wrapper">
        @if ($statusCode === 'pending')
            <p class="pending-message">*承認待ちのため修正はできません。</p>
        @else
            <button type="submit" class="detail-form__button">修正</button>
        @endif
    </div>
</form>
<script>
    const statusCode = @json($statusCode);
    window.laravelErrors = @json($errors->toArray());
    window.oldBreakStarts = @json(old('break_start'));
    window.oldBreakEnds = @json(old('break_end'));
    window.breakDefaults = @json($breaks->map(fn($b) => [
        'start' => optional($b->break_start)->format('H:i'),
        'end' => optional($b->break_end)->format('H:i'),
    ]));
</script>
<script src="{{ asset('js/breaks.js') }}"></script>
<script src="{{ asset('js/time-empty.js') }}"></script>
@endsection