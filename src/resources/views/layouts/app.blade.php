<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title')</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/layouts/common.css') }}">
    @yield('css')
</head>
<body>
    <header class="header">
        <div class="header-logo__inner">
            @if (Auth::guard('web')->check() || in_array(Route::currentRouteName(), ['login', 'register', 'verification.notice']))
                <a href="/attendance">
                    <img src="{{ asset('/images/logo.svg') }}" alt="ロゴ" class="header-logo">
                </a>
            @else
                <a href="/admin/attendance/list">
                    <img src="{{ asset('/images/logo.svg') }}" alt="ロゴ" class="header-logo">
                </a>
            @endif
        </div>
        @if (Auth::guard('web')->check() && auth()->user()->hasVerifiedEmail())
            <nav class="header__nav">
                <ul>
                    <li>
                        <a href="/attendance">
                            <button class="header__button">勤怠</button>
                        </a>
                    </li>
                    <li>
                        <a href="/attendance/list">
                            <button class="header__button">勤怠一覧</button>
                        </a>
                    </li>
                    <li>
                        <a href="/stamp_correction_request/list">
                            <button class="header__button">申請</button>
                        </a>
                    </li>
                    <li>
                        <form action="/logout" method="post">
                            @csrf
                            <button type="submit" class="header__button">ログアウト</button>
                        </form>
                    </li>
                </ul>
            </nav>
        @elseif (Auth::guard('admin')->check())
            <nav class="header__nav">
                <ul>
                    <li>
                        <a href="/admin/attendance/list">
                            <button class="header__button">勤怠一覧</button>
                        </a>
                    </li>
                    <li>
                        <a href="/admin/staff/list">
                            <button class="header__button">スタッフ一覧</button>
                        </a>
                    </li>
                    <li>
                        <a href="/admin/stamp_correction_request/list">
                            <button class="header__button">申請一覧</button>
                        </a>
                    </li>
                    <li>
                        <form action="/admin/logout" method="post">
                            @csrf
                            <button type="submit" class="header__button">ログアウト</button>
                        </form>
                    </li>
                </ul>
            </nav>
        @endif
    </header>
    @yield('content')
</body>
</html>