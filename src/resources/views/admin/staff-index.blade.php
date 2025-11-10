@extends('layouts/app')

@section('title', 'スタッフ一覧画面(管理者)')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/staff-index.css') }}">
@endsection

@section('content')
<div class="staff__content">
    <h1 class="staff__title">スタッフ一覧</h1>
    <table class="staff-table">
        <tr>
            <th>名前</th>
            <th>メールアドレス</th>
            <th>月次勤怠</th>
        </tr>
        @foreach ($users as $user)
            <tr>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>
                    <a href="/admin/attendance/staff/{{ $user->id }}" class="staff-detail__link">詳細</a>
                </td>
            </tr>
        @endforeach
    </table>
</div>
@endsection