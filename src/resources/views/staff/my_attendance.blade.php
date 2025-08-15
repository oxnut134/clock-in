@extends('layouts.header')

@section('css')
<link rel="stylesheet" href="{{ asset('css/my_attendance.css') }}">
@endsection

@section('content')

<body>
    <div class="my_attendance_form">
        <h3 class="my_attendance_form_title">勤怠一覧</h3>
        <table>
            <colgroup>
                <col style="width: 30%;">
                <col style="width: 40%;">
                <col style="width: 30%;">
            </colgroup>
            <tbody>
                <tr style="height:7vh;border-top: 2px solid #eee;font-weight:normal;">
                    <th>
                        <form style="width:100%;height:100%;display:flex;justify-content:flex-start;align-items:center;" action="/attendance/last_month" method="post">
                            @csrf
                            <input type="hidden" name="currentDateTime" value="{{ $currentDateTime }}">
                            <button style="padding-left:8%;">← 前月</button>
                        </form>
                    </th>
                    <th>
                        <input style="border:none;font-size:16px;" type="month" name="date-picker" value="{{ $this_year_month }}">
                    </th>
                    <th>
                        <form style="width:100%;height:100%;display:flex;justify-content:flex-end;align-items:center;" action="/attendance/next_month" method="post">
                            @csrf
                            <input type="hidden" name="currentDateTime" value="{{ $currentDateTime }}">
                            <button style="padding-right:8%;">翌月 →</button>
                        </form>
                    </th>
                    <th></th>
                </tr>
            </tbody>
        </table>
        <table>
            <thead style="height:5vh;">
                <tr>
                    <th style="width: 25%;">日付</th>
                    <th style="width: 15%;">出勤</th>
                    <th style="width: 15%;">退勤</th>
                    <th style="width: 15%;">休憩</th>
                    <th style="width: 15%;">合計</th>
                    <th style="width: 15%;">詳細</th>
                </tr>
            </thead>
            @foreach($jobs as $job)
            <tbody>
                <tr style="height:5vh; border-top: 2px solid #eee;">
                    <td>
                        {{ \Carbon\Carbon::parse($job['date'])->format('m/d') }} ({{ \Carbon\Carbon::parse($job['date'])->isoFormat('ddd') }})
                    </td>
                    <td>
                        @if($job['job_start']){{ \Carbon\Carbon::parse($job['job_start'])->format('H:i') }}@else @endif
                    </td>
                    <td>
                        @if($job['job_finish']){{ \Carbon\Carbon::parse($job['job_finish'])->format('H:i') }}@else @endif
                    </td>
                    <td>
                        {{ floor($job['break_duration'] / 60) }}:{{ str_pad($job['break_duration'] % 60, 2, '0', STR_PAD_LEFT) }}
                    </td>
                    <td>
                        {{ floor($job['job_duration'] / 60) }}:{{ str_pad($job['job_duration'] % 60, 2, '0', STR_PAD_LEFT) }}
                    </td>
                    <td>
                        <a style="text-decoration:none;color:#000;" href=" /attendance/detail/{{ $job['id'] }} ">詳細</a>
                    </td>
                </tr>

                <!--              <tr style="height:5vh;border-top: 2px solid #eee;">
                    <td>{{ $job['date'].$job['day_of_week']}}</td>
                    <td>{{ $job['job_start'] }}</td>
                    <td>{{ $job['job_finish'] }}</td>
                    <td>{{ $job['break_duration'] }}</td>
                    <td>{{ $job['job_duration'] }}</td>
                    <td>
                        <form>
                            <button>詳細</button>
                        </form>
                    </td>
                </tr>
-->
            </tbody>
            @endforeach
        </table>
    </div>
</body>

@endsection
