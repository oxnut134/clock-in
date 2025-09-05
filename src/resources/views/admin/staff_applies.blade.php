@extends('layouts.header_admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/my_applies.css') }}">
@endsection

@section('content')

<body style="background-color:#eee;">
    <div class="my_applies_form">
        <h3 class="my_applies_form_title">申請一覧</h3>
        <table>
            <colgroup>
                <col style="width: 15%;">
                <col style="width: 15%;">
                <col style="width: 70%;">
            </colgroup>
            <tbody>
                <tr style="border-top: 2px solid #eee;font-weight:normal;">
                    <th>
                        <form action="/admin/requests" method="get">
                            <input type="hidden" name="param" value="applied">
                            <button>承認待ち</button>
                        </form>
                    </th>
                    <th>
                        <form action="/admin/requests" method="get">
                            <input type="hidden" name="param" value="approved">
                            <button>承認済み</button>
                        </form>
                    </th>
                    <th></th>
                </tr>
            </tbody>
        </table>
        <table>
            <thead>
                <tr>
                    <th style="width: 25%;">状態</th>
                    <th style="width: 15%;">名前</th>
                    <th style="width: 15%;">対象日時</th>
                    <th style="width: 15%;">申請理由</th>
                    <th style="width: 15%;">申請日時</th>
                    <th style="width: 15%;">詳細</th>
                </tr>
            </thead>
            <tbody>
                @php
                use Carbon\Carbon;
                @endphp

                @foreach($jobs as $job)
                @php
                $job['date'] = Carbon::parse($job['date'])->format('Y/m/d');
                $job['apply_date'] = Carbon::parse($job['apply_date'])->format('Y/m/d');
               @endphp
                <tr style="border-top: 2px solid #eee;">
                    <td>{{ $status }}</td>
                    <td>{{ $job->user['name'] }}</td>
                    <td>{{ $job['date'] }}</td>
                    <td>{{ $job['remark'] }}</td>
                    <td>{{ $job['apply_date'] }}</td>
                    <td>
                        <form action="/admin/requests/{{ $job['id'] }}" method="get">
                            <button>詳細</button>
                        </form>
                    </td>
                </tr>
                @endforeach
        </table>
    </div>
</body>

@endsection
