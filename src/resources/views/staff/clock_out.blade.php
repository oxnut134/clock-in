@extends('layouts.header')

@section('css')
<link rel="stylesheet" href="{{ asset('css/clock_common.css') }}">
@endsection

@section('content')

<body>
    <form action="/attendance" method="post">
        @csrf
        <div class="clock_common_form">
            <div class="clock_common_form_status">退勤済</div>
            <div class="clock_common_form_date">{{ $date.$dayOfWeek}}</div>
            <h1 class="clock_common_form_time">{{ $time }}</h1>
            <div style="width:15%;height:7vh;margin:1%;font-size:17px;display:flex;justify-content:center;">お疲れ様でした。</div>
        </div>
    </form>
</body>
@endsection
