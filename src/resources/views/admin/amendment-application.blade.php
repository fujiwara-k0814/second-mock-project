@extends('layouts/app')

@section('title', '修正申請承認画面(管理者)')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/amendment-application.css') }}">
@endsection

@section('content')
<form action="/admin/stamp_correction_request/approve/{{ $amendment->id }}" 
    method="post" class="amendment-form">
    @csrf
    <h1 class="amendment__title">勤怠詳細</h1>
    <table class="amendment-table">
        <tr class="name-row">
            <th>名前</th>
            <td>{{ $amendment->attendance->user->name }}</td>
        </tr>
        <tr class="date-row">
            <th>日付</th>
            <td>
                <span>{{ $amendment->attendance->date->locale('ja')->isoFormat('YYYY年') }}</span>
                <span>{{ $amendment->attendance->date->locale('ja')->isoFormat('M月D日') }}</span>
            </td>
        </tr>
        <tr class="clock-row">
            <th>出勤・退勤</th>
            <td>
                {{ $amendment->clock_in->Format('H:i') }}
                <span>～</span>
                {{ $amendment->clock_out->Format('H:i') }}
            </td>
        </tr>
        @forelse ($breaks as $index => $break)
            <tr class="break-row">
                <th>
                    {{-- 休憩→休憩2→休憩3を作成するためindex=0除外で以降+1 --}}
                    休憩{{ $index === 0 ? '' : $index + 1 }}
                </th>
                <td>
                    {{ $break->break_start?->format('H:i') }}
                    <span>{{ $break->break_start ? '～' : '' }}</span>
                    {{ $break->break_end?->format('H:i') }}
                </td>
            </tr>
        @empty
            <tr class="break-row">
                <th>休憩</th>
                <td></td>
            </tr>
        @endforelse
        <tr>
            <th>備考</th>
            <td>
                {{ $amendment->comment }}
            </td>
        </tr>
    </table>
    <div class="form-wrapper">
        @if ($amendment->approvalStatus->code === 'approved')
            <p class="approved-message">承認済み</p>
        @else
            <button type="submit" class="amendment-form__button">承認</button>
        @endif
    </div>
</form>
@endsection