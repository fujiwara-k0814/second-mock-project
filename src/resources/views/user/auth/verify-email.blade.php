@extends('layouts.app')

@section('title', 'メール認証誘導画面')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/verify-email.css') }}">
@endsection

@section('content')
<div class="verify__content">
    <div class="verify__inner">
        <p class="verify__message">登録していただいたメールアドレスに認証メールを送付しました。</p>
        <p class="verify__message">メール認証を完了してください。</p>
        <a href="http://localhost:8025" class="verify__button">認証はこちらから</a>
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="verify-send__button">認証メールを再送する</button>
        </form>
    </div>
</div>
@endsection
