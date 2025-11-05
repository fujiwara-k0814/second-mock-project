@extends('layouts/app')

@section('title', '勤怠登録画面')
    
@section('css')
<link rel="stylesheet" href="{{ asset('css/user/time-stamp') }}">    
@endsection

@section('content')
<div class="stamp__content">
    <p class="work-status">{{ $status->label() }}</p>
    <time id="current-date" datetime="" class="current-date">--</time>
    <time id="current-time" datetime="" class="current-time">--:--</time>
    <form action="/attendance" method="post" class="stamp-form">
        @csrf
        @switch($status)
            @case(\App\Enums\AttendanceStatus::WORKING)
                <div class="stamp__button-wrapper">
                    <button type="submit" class="stamp__button" name="finish">
                        退勤
                    </button>
                    <button type="submit" class="stamp__button stamp__button--white">
                        休憩入
                    </button>
                </div>
                @break
            @case(\App\Enums\AttendanceStatus::ON_BREAK)
                <button type="submit" class="stamp__button stamp__button--white">
                    休憩戻
                </button>
                @break
            @case(\App\Enums\AttendanceStatus::FINISHED)
                <p class="clock-out">お疲れ様でした。</p>
                @break
            @default
                <button type="submit" class="stamp__button">
                    出勤
                </button>
        @endswitch
        <input type="hidden" name="status" value="{{ $status->value }}">
    </form>
</div>
<script src="{{ asset('js/datetime.js') }}"></script>
@endsection