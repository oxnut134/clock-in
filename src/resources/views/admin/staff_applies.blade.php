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
                    <th style="width: 15%;">状態</th>
                    <th style="width: 15%;">名前</th>
                    <th style="width: 20%;">対象日時</th>
                    <th style="width: 20%;">申請理由</th>
                    <th style="width: 20%;">申請日時</th>
                    <th style="width: 10%;">詳細</th>
                </tr>
            </thead>
            <tbody>
                <tr style="border-top: 2px solid #eee;">
                    <td>承認待ち</td>
                    <td>西玲奈</td>
                    <td>2023/06/01</td>
                    <td>遅延のため</td>
                    <td>2023/06/02</td>
                    <td>
                        <form>
                            <button>詳細</button>
                        </form>
                    </td>
                </tr>
            </tbody>
            <tbody>
                <tr style="border-top: 2px solid #eee;">
                    <td>承認待ち</td>
                    <td>山田太郎</td>
                    <td>2023/06/01</td>
                    <td>遅延のため</td>
                    <td>2023/06/02</td>
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
