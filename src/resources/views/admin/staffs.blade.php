@extends('layouts.header_admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/staffs.css') }}">
@endsection

@section('content')

<body>
    <div class="todays_staffs_form">
        <h3 class="todays_staffs_form_title" >スタッフ一覧</h3>
        <div style="height:7vh;"></div>
        <table class="todays_staffs_form_this_month_block">
            <thead >
                <tr style="height:4vh;">
                    <th style="width: 10%;background-color:#eee;"></th>
                    <th style="width: 25%;">名前</th>
                    <th style="width: 45%;">メールアドレス</th>
                    <th style="width: 10%;">詳細</th>
                    <th style="width: 10%;background-color:#eee;"></th>
                </tr>
            </thead>
            @foreach($staffs as $staff)
            <tbody>
                <tr style="height:4vh;border-top: 2px solid #eee;">
                    <td style="background-color:#eee;"></td>
                    <td>{{ $staff['name'] }}</td>
                    <td>{{ $staff['email'] }}</td>
                    <td >
                        <form>
                            <button>詳細</button>
                        </form>
                    </td>
                    <td style="background-color:#eee;"></td>
                </tr>
            </tbody>
            @endforeach
        </table>
    </div>
</body>

@endsection
