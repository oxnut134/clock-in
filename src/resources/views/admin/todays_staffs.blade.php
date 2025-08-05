@extends('layouts.header')

@section('css')
<link rel="stylesheet" href="{{ asset('css/todays_staffs.css') }}">
@endsection

@section('content')

<body>
    <div class="todays_staffs_form">
        <h3 class="todays_staffs_form_title">申請一覧</h3>
        <table class="todays_staffs_form_this_month_block">
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
                            <button style="padding-left:8%;">← 前日</button>
                        </form>
                    </th>
                    <th>
                        <input style="border:none;font-size:16px;" aria-checked="" type="date" name="date-picker" value="2023-06-01">
                    </th>
                    <th >
                        <form style="width:100%;height:100%;display:flex;justify-content:flex-end;align-items:center;">
                            @csrf
                            <button style="padding-right:8%;">翌日 →</button>
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
            <tbody>
                <tr style="border-top: 2px solid #eee;">
                    <td>山田 太郎</td>
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
                    <td>西 玲奈</td>
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
                    <td>秋田 朋美</td>
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
    </div>
</body>

@endsection
