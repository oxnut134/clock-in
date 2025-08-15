<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Job;
use App\Models\BreakTime;

class ProMyAttendanceController extends Controller
{

    public function showClockIn(Request $request)
    {

        $currentDateTime = Carbon::now();
        // 現在の日時、曜日、時刻を取得
        $date = $currentDateTime->format('Y年n月j日'); // 例: 2025年8月6日
        $dayOfWeek = $currentDateTime->isoFormat('(ddd)'); // 例: (水)
        $time = $currentDateTime->format('H:i'); // 時刻秒なし (例: 11:26)

        return view('/staff/clock_in', [
            'date' => $date,
            'dayOfWeek' => $dayOfWeek,
            'time' => $time,
            'ClockInDateTime'=>$currentDateTime,
        ]);
    }
    public function putClockIn(Request $request)
    {
        // 本日の日時を取得
        //$today = Carbon::now()->format('Y年n月j日');
        $today = "2025年9月2日"; //デバッグ用

        $todayOriginal = $today;
        $user = 1; //本番ではAuth::id();となる

        // 条件: user_id = 1 かつ date = 本日 のレコードが存在しない場合
        $recordExists = Job::where('user_id', $user)
            ->where('date', $today)
            ->exists(); // レコードが存在するか確認
        if (!$recordExists) {
            $last_record = Job::where('user_id', $user)
                ->latest('id')->first();
            //$Last_recordが存在するときのみ実行（レコード数<=1では実行しない）
            if ($last_record != null) {
                $last_date = $last_record->date;
                // $last_date を Carbon インスタンスに変換

                $lastDateCarbon = Carbon::createFromFormat('Y年n月j日', $last_date);

                // $start_day: $last_date の1日後
                $start_day = $lastDateCarbon->addDay()->format('Y年n月j日');

                // $yesterday: $today の1日前
                $todayCarbon = Carbon::createFromFormat('Y年n月j日', $today);
                $yesterday = $todayCarbon->subDay()->format('Y年n月j日');

                // $start_day と $yesterday を Carbonインスタンスに変換
                $startDate = Carbon::createFromFormat('Y年n月j日', $start_day);
                $endDate = Carbon::createFromFormat('Y年n月j日', $yesterday);

                //dd($start_day." ". $yesterday);
                // 1日ごとにレコード追加
                while ($startDate->lte($endDate)) {
                    // 現在の日付をフォーマット（0なし表示に）
                    $currentDate = $startDate->format('Y年n月j日');
                    // 曜日を計算 (例: "(月)")
                    $dayOfWeek = $startDate->isoFormat('(ddd)');
                    // Jobを作成
                    Job::create([
                        'user_id' => $user,
                        'date' => $currentDate,
                        'day_of_week' => $dayOfWeek,
                        'job_start' => null,
                        'job_status' => null
                        // 他の必要なカラムを追加
                    ]);

                    // 日付を1日進める
                    $startDate->addDay();
                }
            }

            $currentDateTime = Carbon::now();
            // 新しいレコードを作成
            Job::create([
                'user_id' => $user,
                'date' => $todayOriginal,
                'day_of_week' => $request->dayOfWeek,
                'job_start' => $currentDateTime->format('H:i:s'),
                'job_status' => "normal"
                // 他の必要なカラムを追加
            ]);

            $current_record = Job::where('user_id', 1)
                ->where('date', $today)
                ->first(); // レコード取得


            return view('/staff/clock_break', [
                'date' => $request->date,
                'dayOfWeek' => $request->dayOfWeek,
                'time' => $request->time,
                'job_id' => $current_record->id
            ]);
        } else {
            ///*
            $message = "本日は退勤済みです。";
            return view('/staff/temporary_message', [
                'message' => '本日は退勤済みです。',
            ]);
            //*/
            /*
            $currentDateTime = Carbon::now();//job_start時間取得用
            // 新しいレコードを作成
            Job::create([
                'user_id' => $user,
                'date' => $todayOriginal,
                'day_of_week' => $request->dayOfWeek,
                'job_start' => $currentDateTime->format('H:i:s'),
                'job_status' => "normal"
                // 他の必要なカラムを追加
            ]);


            $current_record = Job::where('user_id', 1)
                ->where('date', $today)
                ->first(); // レコード取得


            return view('/staff/clock_break', [
                'date' => $request->date,
                'dayOfWeek' => $request->dayOfWeek,
                'time' => $request->time,
                'job_id' => $current_record->id
            ]);
            */
        }
    }
    public function putClockBreak(Request $request)
    {
        //dd($request);
        $currentDateTime = Carbon::now();
        $breakTime = BreakTime::create([
            'job_id' => $request->job_id,
            'break_start' => $currentDateTime->format('H:i:s'),
            'break_status' => "normal"
        ]);
        $break_id = $breakTime->id;

        return view('/staff/clock_return', [
            'date' => $request->date,
            'dayOfWeek' => $request->dayOfWeek,
            'time' => $request->time,
            'job_id' => $request->job_id,
            'break_id' => $break_id
        ]);
    }
    public function putClockReturn(Request $request)
    {
        //dd($request);
        $currentDateTime = Carbon::now();
        $breakTime = BreakTime::find($request->break_id);
        $breakTime->updateFinish($currentDateTime->format('H:i:s'));

        return view('/staff/clock_break', [
            'date' => $request->date,
            'dayOfWeek' => $request->dayOfWeek,
            'time' => $request->time,
            'job_id' => $request->job_id,

        ]);
    }
    public function putClockOut(Request $request)
    {
        //dd($request);
        $currentDateTime = Carbon::now();
        $job = Job::find($request->job_id);
        $job->updateFinish($currentDateTime->format('H:i:s'));

        return view('/staff/clock_out', [
            'date' => $request->date,
            'dayOfWeek' => $request->dayOfWeek,
            'time' => $request->time,
        ]);
    }
    public function getMyList()
    {
        $currentDateTime = session('currentDateTime');
        //dd($currentDateTime);
        if ($currentDateTime ==  null) {
            $currentDateTime = Carbon::now();
        }
        $thisYearMonth = $currentDateTime->format('Y年n月');
        $jobs = Job::with('breakTime')
            ->where('user_id', 1) //本番はAuth::id();
            ->where('date', 'LIKE', $thisYearMonth . '%')
            ->get();
        //dd($jobs);
        foreach ($jobs as $job) {
            $breakDuration = 0;
            foreach ($job->breakTime as $break) {

                $breakTime = BreakTime::find($break->id);
                $breakDuration += $breakTime->calculateDuration();
                echo "<script>console.log('User ID: {$job->user_id}, Job ID: {$break->job_id}');</script>";
            }
            $job = Job::find($job->id);
            $jobDuration = $job->calculateDuration() - $breakDuration;

            $job->break_duration = $breakDuration;
            $job->job_duration = $jobDuration;
            $job->save();
        }



        $thisYearMonth = $currentDateTime->format('Y-m');
        //dd($thisYearMonth);
        return view('/staff/my_attendance', [
            'this_year_month' => $thisYearMonth,
            'jobs' => $jobs,
            'currentDateTime' => $currentDateTime,


        ]);
    }
    public function ShowLastMonth(Request $request)
    {
        $currentDateTime = $request->currentDateTime;
        $lastMonth = Carbon::parse($currentDateTime)->subMonth(); //前月
        //dd($lastMonth);
        return redirect()->route('attendance.list')->with(['currentDateTime' => $lastMonth]);
    }
    public function ShowNextMonth(Request $request)
    {
        $currentDateTime = $request->currentDateTime;
        $nextMonth = Carbon::parse($currentDateTime)->addMonth(); //翌月
        //dd($lastMonth);
        return redirect()->route('attendance.list')->with(['currentDateTime' => $nextMonth]);
    }
}
