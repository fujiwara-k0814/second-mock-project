@extends('layouts/app')

@section('title', 'スタッフ一覧画面(管理者)')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/staff-index.css') }}">
@endsection

@section('content')
<div class="staff__content">
    <h1 class="staff__title">スタッフ一覧</h1>
    <table class="staff-table">
        <tr class="header-row">
            <th class="table-header table-header-name">名前</th>
            <th class="table-header table-header-email">メールアドレス</th>
            <th class="table-header table-header-month">月次勤怠</th>
        </tr>
        @foreach ($users as $user)
            <tr class="data-row">
                <td class="table-data table-data-name">{{ $user->name }}</td>
                <td class="table-data">{{ $user->email }}</td>
                <td class="table-data table-data-month">
                    <a href="/admin/attendance/staff/{{ $user->id }}" 
                        class="staff-detail__link">詳細</a>
                </td>
            </tr>
        @endforeach
    </table>
</div>
@endsection