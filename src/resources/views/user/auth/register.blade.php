@extends('layouts/app')

@section('title', '会員登録')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/register.css') }}">
@endsection

@section('content')
<div class="register__content">
    <h1 class="register__title">会員登録</h1>
    <form action="/register" method="post" class="register-form">
        @csrf
        <label for="name" class="register-form__label">名前</label>
        <input type="text" name="name" id="name" class="register-form__input" value="{{ old('name') }}">
        <div class="register-form__error">
            @error('name')
                {{ $message }}
            @enderror
        </div>
        <label for="email" class="register-form__label">メールアドレス</label>
        <input type="text" name="email" id="email" class="register-form__input" value="{{ old('email') }}">
        <div class="register-form__error">
            @error('email')
                {{ $message }}
            @enderror
        </div>
        <label for="password" class="register-form__label">パスワード</label>
        <input type="text" name="password" id="password" class="register-form__input">
        <div class="register-form__error">
            @error('password')
                {{ $message }}
            @enderror
        </div>
        <label for="password_confirmation" class="register-form__label">パスワード確認</label>
        <input type="text" name="password_confirmation" id="password_confirmation" class="register-form__input">
        <div class="register-form__error">
            @error('password_confirmation')
                {{ $message }}
            @enderror
        </div>
        <button type="submit" class="register-form__button">登録する</button>
    </form>
    <a href="/login" class="login-link">ログインはこちら</a>
</div>
@endsection