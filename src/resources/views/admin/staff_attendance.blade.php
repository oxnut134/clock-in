@extends('layouts.header')

@section('css')
<link rel="stylesheet" href="{{ asset('css/staff_attendance.css') }}">
@endsection

@section('content')

<body>
    <div class="my_attendance_form">
        <h3 class="my_attendance_form_title">西玲奈さんの勤怠</h3>
       <table >
            <colgroup>
                <col style="width: 30%;">
                <col style="width: 40%;">
                <col style="width: 30%;">
            </colgroup>
            <tbody>
                <tr style="border-top: 2px solid #eee;font-weight:normal;">
                    <th >
                        <form style="width:100%;height:100%;display:flex;justify-content:flex-start;align-items:center;">
                            @csrf
                            <button style="padding-left:8%;">← 前月</button>
                        </form>
                    </th>
                    <th>
                        <input style="border:none;font-size:16px;" aria-checked="" type="month" name="date-picker" value="2023-06">
                    </th>
                    <th >
                        <form style="width:100%;height:100%;display:flex;justify-content:flex-end;align-items:center;">
                            @csrf
                            <button style="padding-right:8%;">翌月 →</button>
                        </form>
                    </th>
                    <th></th>
                </tr>
            </tbody>
        </table>
        <table>
            <thead>
                <tr>
                    <th style="width: 25%;">日付</th>
                    <th style="width: 15%;">出勤</th>
                    <th style="width: 15%;">退勤</th>
                    <th style="width: 15%;">休憩</th>
                    <th style="width: 15%;">合計</th>
                    <th style="width: 15%;">詳細</th>
                </tr>
            </thead>
            <tbody>
                <tr style="border-top: 2px solid #eee;">
                    <td>06/01(木)</td>
                    <td>09:00</td>
                    <td>18:00</td>
                    <td>1:00</td>
                    <td>8:00</td>
                    <td>
                        <form>
                            <button>詳細</button>
                        </form>
                    </td>
                </tr>
            </tbody>
            <tbody>
                <tr style="border-top: 2px solid #eee;">
                    <td>06/01(木)</td>
                    <td>09:00</td>
                    <td>18:00</td>
                    <td>1:00</td>
                    <td>8:00</td>
                    <td>
                        <form>
                            <button>詳細</button>
                        </form>
                    </td>
                </tr>
            </tbody>
            <tbody>
                <tr style="border-top: 2px solid #eee;">
                    <td>06/01(木)</td>
                    <td>09:00</td>
                    <td>18:00</td>
                    <td>1:00</td>
                    <td>8:00</td>
                    <td>
                        <form>
                            <button>詳細</button>
                        </form>
                    </td>
                </tr>
            </tbody>
        </table>
                <div style="width:65%;height:10vh;">
            <form style="width:100%;height:100%;display:flex;justify-content:flex-end;align-items:flex-end;">
                @csrf
                <button style="width:15%;height:5vh;border-radius:5px;background-color:#000;color:#fff;">ＣＳＶ出力</button>
            </form>
        </div>

    </div>
</body>

@endsection
