@extends('layouts.header')

@section('css')
<link rel="stylesheet" href="{{ asset('css/clock_common.css') }}">
@endsection

@section('content')

<body>
    <form  action="/attendance" method="post">
        @csrf
        <div class="clock_common_form">
            <div class="clock_common_form_status">勤務外</div>
            <div class="clock_common_form_date">2023年6月1日(木)</div>
            <h1 class="clock_common_form_time">08:00</h1>
            <button style="width:10%;height:7vh;border-radius:10%;margin:1%;background-color:#000;color:#fff;font-size:20px;">出勤</button>
        </div>
    </form>
</body>
@endsection
