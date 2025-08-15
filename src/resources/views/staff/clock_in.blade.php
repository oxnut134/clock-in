@extends('layouts.header')

@section('css')
<link rel="stylesheet" href="{{ asset('css/clock_common.css') }}">
@endsection

@section('content')

<body>
    <form action="/" method="post">
        @csrf
@php
    use Carbon\Carbon;

    $date = Carbon::parse($date)->format('Y年n月j日');
    $dayOfWeek ='('. Carbon::parse($dayOfWeek)->isoFormat('ddd').')';
    $time = Carbon::parse($time)->format('H:i');
@endphp
        <div class="clock_common_form">
            <div class="clock_common_form_status">勤務外</div>
            <div class="clock_common_form_date">{{ $date.$dayOfWeek}}</div>
            <h1 class="clock_common_form_time">{{ $time }}</h1>
            <input type="hidden" name="date" value="{{ $date }}">
            <input type="hidden" name="dayOfWeek" value="{{ $dayOfWeek }}">
            <input type="hidden" name="time" value="{{ $time }}">
            <button style="width:10%;height:7vh;border-radius:10%;margin:1%;background-color:#000;color:#fff;font-size:20px;">出勤</button>
        </div>
    </form>
</body>
@endsection
