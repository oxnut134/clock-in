@extends('layouts.header')

@section('css')
<link rel="stylesheet" href="{{ asset('css/clock_common.css') }}">
@endsection

@section('content')

<body>
    <form  action="/attendance" method="post">
        @csrf
        <div class="clock_common_form">
            <div class="clock_common_form_status">勤務中</div>
            <div class="clock_common_form_date">2023年6月1日(木)</div>
            <h1 class="clock_common_form_time">08:00</h1>
            <div style="width:25%;display:flex;justify-content:space-between">
                <button style="width:40%;height:7vh;border-radius:10%;margin:3%;background-color:#000;color:#fff;font-size:20px;">退勤</button>
                <button style="width:40%;height:7vh;border:none;margin:3%;background-color:#fff;color:#000;font-size:20px;">休憩入</button>
            </div>
        </div>
    </form>
</body>
@endsection
