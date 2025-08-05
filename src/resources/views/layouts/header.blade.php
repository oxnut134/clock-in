<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>勤怠管理</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/header.css') }}">
    @yield('css')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body>
    @if (Auth::check())
    <header class="clock-in_header" style="display:flex;justify-content:space-between;align-items: center;">

        @else
        <header class="clock-in_header" style="display:flex;justify-content:flex-start;align-items:center;">

            @endif

            <img class="clock-in_header_logo" src=" {{ asset('storage/logo.svg')}}" alt="error">

            @if (Auth::check())
            <div class="clock-in_header_button_block" style="width:35%;">
                <form action="" method="post">
                    @csrf
                    <button class="clock-header_buttons">勤怠</button>
                </form>
                <form action="" method="post">
                    @csrf
                    <button class="clock-header_buttons">勤怠一覧</button>
                </form>
                <form action="" method="post">
                    @csrf
                    <button class="clock-header_buttons">申請</button>
                </form>
                <form action="{{ route('logout') }}" method="post">
                    @csrf
                    <button class="clock-header_buttons">ログアウト</button>
                </form>
            </div>
            @endif
            <meta name="csrf-token" content="{{ csrf_token() }}">
        </header>
        <main>
            @yield('content')
        </main>

</body>

</html>
