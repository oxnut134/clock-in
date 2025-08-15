@extends('layouts.header')

@section('css')
<link rel="stylesheet" href="{{ asset('css/my_applies.css') }}">
@endsection

@section('content')

<body>
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
                        <form action="/stamp_correction_request/list/applied" method="get">
                            <button>承認待ち</button>
                        </form>
                    </th>
                    <th>
                        <form action="/stamp_correction_request/list/approved" method="get">
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
                //dd($job['updated_at']);
                $job['date'] = Carbon::parse($job['date'])->format('Y/m/d');
                $updatedAt = $job['updated_at']->format('Y/m/d');
                @endphp
                <tr style="border-top: 2px solid #eee;">
                    <td>{{ $status }}</td>
                    <td>{{ $user['name'] }}</td>
                    <td>{{ $job['date'] }}</td>
                    <td>{{ $job['remark'] }}</td>
                    <td>{{ $updatedAt }}</td>
                    <td>
                        <form action="/attendance/detail/{{ $job['id'] }}" method="get">
                            <button>詳細</button>
                        </form>
                    </td>
                </tr>
                @endforeach
        </table>
    </div>
</body>

@endsection
