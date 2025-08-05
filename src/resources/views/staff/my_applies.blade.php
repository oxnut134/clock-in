@extends('layouts.header')

@section('css')
<link rel="stylesheet" href="{{ asset('css/my_applies.css') }}">
@endsection

@section('content')

<body>
    <div class="my_applies_form">
        <h3 class="my_applies_form_title">申請一覧</h3>
        <table >
            <colgroup>
                <col style="width: 15%;">
                <col style="width: 15%;">
                <col style="width: 70%;">
            </colgroup>
            <tbody>
                <tr style="border-top: 2px solid #eee;font-weight:normal;">
                    <th >
                        <button>承認待ち</button>
                    </th>
                    <th>
                        <button>承認済み</button>
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
    </div>
</body>

@endsection
