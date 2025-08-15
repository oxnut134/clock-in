@extends('layouts.header')

@section('css')
<link rel="stylesheet" href="{{ asset('css/clock_common.css') }}">
@endsection

@section('content')

<body>
    <form action="/clock/return" method="post">
        @csrf
        <div class="clock_common_form">
            <div class="clock_common_form_status">休憩中</div>
            <div class="clock_common_form_date">{{ $date.$dayOfWeek}}</div>
            <h1 class="clock_common_form_time">{{ $time }}</h1>
            <input type="hidden" name="date" value="{{ $date }}">
            <input type="hidden" name="dayOfWeek" value="{{ $dayOfWeek }}">
            <input type="hidden" name="time" value="{{ $time }}">
            <input type="hidden" name="job_id" value="{{ $job_id }}">
            <input type="hidden" name="break_id" value="{{ $break_id }}">

            <button style="width:10%;height:7vh;border:none;margin:1%;background-color:#fff;color:#000;font-size:20px;">休憩戻</button>
        </div>
    </form>
</body>
@endsection
