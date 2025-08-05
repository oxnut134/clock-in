@extends('layouts.header')

@section('css')
<link rel="stylesheet" href="{{ asset('css/clock_common.css') }}">
@endsection

@section('content')

<body>
    <form  action="/attendance" method="post">
        @csrf
        <div class="clock_common_form">
            <div class="clock_common_form_status">休憩中</div>
            <div class="clock_common_form_date">2023年6月1日(木)</div>
            <h1 class="clock_common_form_time">08:00</h1>
            <button style="width:10%;height:7vh;border:none;margin:1%;background-color:#fff;color:#000;font-size:20px;">休憩戻</button>
        </div>
    </form>
</body>
@endsection
