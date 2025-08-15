@extends('layouts.header_admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/my_detail.css') }}">
@endsection

@section('content')

<body>
    <form action="/admin/apply" method="post">
        @csrf
        <div class="my_attendance_form">
            <h3 class="my_attendance_form_title">勤怠詳細</h3>
            <table>
                <colgroup>
                    <col style="width: 40%;">
                    <col style="width: 20%;">
                    <col style="width: 5%;">
                    <col style="width: 15%;">
                    <col style="width: 20%;">
                </colgroup>
                <tbody>
                    <tr style="border-top: 2px solid #eee;">
                        <th class="first_column_adjust">名前</th>
                        <th>{{ $name }}</th>
                        <th></th>
                        <th></th>
                        <th></th>
                    </tr>
                    <tr style="border-top: 2px solid #eee;">
                        <th class="first_column_adjust">日付</th>
                        <th>{{ \Carbon\Carbon::parse($date)->format('Y') }}年</th>
                        <th></th>
                        <th>{{ \Carbon\Carbon::parse($date)->format('n月j日') }}</th>
                        <th></th>
                    </tr>
                    <tr style="border-top: 2px solid #eee;">
                        <th class="first_column_adjust">出勤・退勤</th>
                        <th>
                            <input type="text" style="width:100%;border:none;text-align:center;" name="job_start" value="@if($job_start) {{ \Carbon\Carbon::parse($job_start)->format('H:i') }}@else  @endif">
                        </th>
                        <th>～</th>
                        <th>
                            <input type="text" style="width:100%;border:none;text-align:center;" name="job_finish" value="@if($job_finish){{ \Carbon\Carbon::parse($job_finish)->format('H:i') }}@else  @endif">
                        </th>
                        <th></th>
                    </tr>
                    @foreach($breakTimes as $index => $breakTime)
                    <tr style="border-top: 2px solid #eee;">
                        @if($index==0)
                        <th class="first_column_adjust">休憩</th>
                        @else
                        <th class="first_column_adjust">休憩{{ $index+1 }}</th>
                        @endif
                        <input type="hidden" name="breakTimes[{{ $index }}][id]" value="{{ $index+1 }}">
                        <th>
                            <input type="text" style="width:100%;border:none;text-align:center;" name="breakTimes[{{ $index }}][break_start]" value="@if($breakTime['break_start']){{ \Carbon\Carbon::parse($breakTime['break_start'])->format('H:i') }}@else  @endif">
                            <input type="hidden" name="breakTimes[{{ $index }}][break_id]" value="{{ $breakTime['id'] }}">
                            <input type="hidden" name="breakTimes[{{ $index }}][job_id]" value="{{ $job_id }}">
                        </th>
                        <th>～</th>
                        <th>
                            <input type="text" style="width:100%;border:none;text-align:center;" name="breakTimes[{{ $index }}][break_finish]" value="@if($breakTime['break_finish']){{ \Carbon\Carbon::parse($breakTime['break_finish'])->format('H:i') }}@else  @endif">
                            <input type="hidden" name="breakTimes[{{ $index }}][break_id]" value="{{ $breakTime['id'] }}">
                            <input type="hidden" name="breakTimes[{{ $index }}][job_id]" value="{{ $job_id }}">

                        </th>
                        <th></th>
                    </tr>
                    @endforeach
                    <tr style="border-top: 2px solid #eee;">
                        <th class="first_column_adjust">備考</th>
                        <th style="font-size:13px;">
                            <input type="text" style="width:200%;border:none;text-align:center;font-size:15px;" name="remark" value="{{ $remark }}">
                        </th>
                        <th></th>
                        <th></th>
                        <th></th>
                    </tr>
                </tbody>
            </table>
            <div style="width:60%;height:10vh;">
                <div style="width:100%;height:100%;display:flex;justify-content:flex-end;align-items:flex-end;">
                    @if($job['job_status']=="normal" || $job['job_status']=="approved" )
                    <input type="hidden" name="id" value="{{ $job['id'] }}">
                    <!--<input type="hidden" name="breakTimes" value="{{ $breakTimes }}">-->
                    <button style="width:15%;height:6vh;border-radius:5px;background-color:#000;color:#fff;">修正</button>
                    @elseif($job['job_status']=="applied" )
                    <div style="width:40%;height:6vh;display:flex;justify-content:flex-end;align-items:flex-start;font-size:15px;">”未承認のため修正はできません”</div>
                    @else
                    <div style="width:40%;height:6vh;display:flex;justify-content:flex-end;align-items:flex-start;font-size:15px;">”無効な勤怠データです。”</div>
                    @endif
                </div>
            </div>
        </div>
    </form>
</body>
@endsection
