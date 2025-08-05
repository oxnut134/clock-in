@extends('layouts.header')

@section('css')
<link rel="stylesheet" href="{{ asset('css/todays_staff_detail.css') }}">
@endsection

@section('content')

<body>
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
                    <th>西　玲奈</th>
                    <th></th>
                    <th></th>
                    <th></th>
                </tr>
                <tr style="border-top: 2px solid #eee;">
                    <th class="first_column_adjust">日付</th>
                    <th>2023年</th>
                    <th></th>
                    <th>6月1日</th>
                    <th></th>
                </tr>
                <tr style="border-top: 2px solid #eee;">
                    <th class="first_column_adjust">出勤・退勤</th>
                    <th>09:00</th>
                    <th>～</th>
                    <th>18:00</th>
                    <th></th>
                </tr>
                <tr style="border-top: 2px solid #eee;">
                    <th class="first_column_adjust">休憩</th>
                    <th>12:00</th>
                    <th>～</th>
                    <th>13:00</th>
                    <th></th>
                </tr>
                <tr style="border-top: 2px solid #eee;">
                    <th class="first_column_adjust">休憩２</th>
                    <th></th>
                    <th>～</th>
                    <th></th>
                    <th></th>
                </tr>
                <tr style="border-top: 2px solid #eee;">
                    <th class="first_column_adjust">備考</th>
                    <th style="font-size:13px;">電車遅延のため</th>
                    <th></th>
                    <th></th>
                    <th></th>
                </tr>
            </tbody>
        </table>
        <div style="width:60%;height:10vh;">
            <form style="width:100%;height:100%;display:flex;justify-content:flex-end;align-items:flex-end;">
                @csrf
                <button style="width:15%;height:6vh;border-radius:5px;background-color:#000;color:#fff;">承認</button>
            </form>
        </div>
    </div>
</body>

@endsection
