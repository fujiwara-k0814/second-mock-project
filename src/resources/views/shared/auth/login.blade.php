@extends('layouts/app')

@if (Route::currentRouteName('login'))
    @section('title', 'ログイン画面')
@else
    @section('title', 'ログイン画面(管理者)')
@endif

@section('css')
<link rel="stylesheet" href="{{ asset('css/shared/login.css') }}">
@endsection

@section('content')
<form action="{{ Route::currentRouteName('login') ? asset('/login') : asset('/admin/login') }}" 
    method="post" class="login-form">
    @csrf
    <h1 class="login__title">{{ Route::currentRouteName('login') ? 'ログイン' : '管理者ログイン' }}</h1>
    <label for="email" class="login-form__label">メールアドレス</label>
    <input type="text" name="email" id="email" class="login-form__input" value="{{ old('email') }}">
    <div class="login-form__error">
        @error('email')
            {{ $message }}
        @enderror
    </div>
    <label for="password" class="login-form__label">パスワード</label>
    <input type="text" name="password" id="password" class="login-form__input">
    <div class="login-form__error">
        @error('password')
            {{ $message }}
        @enderror
    </div>
    <button type="submit" class="login-form__button">
        {{ Route::currentRouteName('login') ? 'ログインする' : '管理者ログインする' }}
    </button>
    @if (Route::currentRouteName('login'))
    <a href="/register" class="register-link">会員登録はこちら</a>
    @endif
</form>
@endsection