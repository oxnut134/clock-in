@extends('layouts.header')

@section('css')
<link rel="stylesheet" href="{{ asset('css/clock_common.css') }}">
@endsection

@section('content')

<body>
    <div class="clock_common_form">
        <div class="clock_common_form_status">勤務中</div>
        <div class="clock_common_form_date">{{ $date.$dayOfWeek}}</div>
        <h1 class="clock_common_form_time">{{ $time }}</h1>
        <div style="width:25%;display:flex;justify-content:space-between">
            <form style="width:100%;" action="/clock/out" method="post" onsubmit="return preventMultipleClicks(this)">
                @csrf

                <input type="hidden" name="date" value="{{ $date }}">
                <input type="hidden" name="dayOfWeek" value="{{ $dayOfWeek }}">
                <input type="hidden" name="time" value="{{ $time }}">
                <input type="hidden" name="job_id" value="{{ $job_id }}">

                <button type="submit" style="width:100%;height:7vh;border-radius:10%;margin:3%;background-color:#000;color:#fff;font-size:20px;">退勤</button>
            </form>

            <form style="width:100%;" action="/clock/break" method="post" onsubmit="return preventMultipleClicks(this)">
                @csrf
                <input type="hidden" name="date" value="{{ $date }}">
                <input type="hidden" name="dayOfWeek" value="{{ $dayOfWeek }}">
                <input type="hidden" name="time" value="{{ $time }}">
                <input type="hidden" name="job_id" value="{{ $job_id }}">

                <button type="submit" style="width:100%;height:7vh;border:none;margin:3%;background-color:#fff;color:#000;font-size:20px;">休憩入</button>
            </form>

            <script>
                function preventMultipleClicks(form) {
                    // フォーム内の送信ボタンを取得
                    const button = form.querySelector("button[type='submit']");
                    if (button) {
                        // ボタンを無効化
                        button.disabled = true;
                        button.innerText = "処理中...";
                    }

                    // フォーム送信を許可
                    return true;
                }
            </script>

        </div>
    </div>
</body>
@endsection
