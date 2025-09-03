@extends('layouts.header_admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/todays_staffs.css') }}">
@endsection

@section('content')

<body style="background-color:#eee;">
    <div class="todays_staffs_form">
        @php
        $date_title = \Carbon\Carbon::createFromFormat('Y-m-d', $date)->format('Y年n月j日');
        @endphp
        <h3 class="todays_staffs_form_title">
            {{ $date_title }} の勤怠
        </h3>
        <table class="todays_staffs_form_this_month_block">
            <colgroup>
                <col style="width: 30%;">
                <col style="width: 40%;">
                <col style="width: 30%;">
            </colgroup>
            <tbody>
                <tr style="border-top: 2px solid #eee;font-weight:normal;">
                    <th>
                        <form style="width:100%;height:100%;display:flex;justify-content:flex-start;align-items:center;" action="/admin/attendances/yesterday" method="post">
                            @csrf
                            <input type="hidden" name="date" value="{{ $date }}">
                            <button style="padding-left:8%;">← 前日</button>
                        </form>
                    </th>
                    <th>
                        <input style="border:none;font-size:16px;" aria-checked="" type="date" name="date-picker" value="{{ $date }}">
                    </th>
                    <th>
                        <form style="width:100%;height:100%;display:flex;justify-content:flex-end;align-items:center;" action="/admin/attendances/tomorrow" method="post">
                            @csrf
                            <input type="hidden" name="date" value="{{ $date }}">
                            <button style=" padding-right:8%;">翌日 →</button>
                        </form>
                    </th>
                    <th></th>
                </tr>
            </tbody>
        </table>
        <table>
            <thead>
                <tr>
                    <th style="width: 25%;">名前</th>
                    <th style="width: 15%;">出勤</th>
                    <th style="width: 15%;">退勤</th>
                    <th style="width: 15%;">休憩</th>
                    <th style="width: 15%;">合計</th>
                    <th style="width: 15%;">詳細</th>
                </tr>
            </thead>
            @php
            use Carbon\Carbon;
            @endphp
            @foreach($jobs as $job)
            @php
            $job['date'] = Carbon::parse($job['date'])->format('Y/m/d');
            $job_start = $job->job_start ? Carbon::parse($job['job_start'])->format('H:i') : null;
            $job_finish = $job->job_finish ? Carbon::parse($job['job_finish'])->format('H:i') : null;
            $break_duration = floor($job['break_duration'] / 60) . ':' . str_pad($job['break_duration'] % 60, 2, '0', STR_PAD_LEFT);
            $job_duration = floor($job['job_duration'] / 60) . ':' . str_pad($job['job_duration'] % 60, 2, '0', STR_PAD_LEFT);
            @endphp
            <tbody>
                <tr style="border-top: 2px solid #eee;">
                    <td>{{ $job->user->name ?? '未設定' }}</td>
                    <td>{{ $job_start }}</td>
                    <td>{{ $job_finish }}</td>
                    <td>
                        @if($job['break_duration']!=0)
                        {{ $break_duration }}
                        @endif
                    </td>
                    <td>
                        @if($job['break_duration']!=0)
                        {{ $job_duration }}
                        @endif
                    </td>
                    <td>
                        <form action="/admin/attendances/{{ $job['id'] }}" method="get">
                            <button>詳細</button>
                        </form>
                    </td>
                </tr>
            </tbody>
            @endforeach
        </table>
    </div>
</body>

@endsection
