<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Job;
use App\Models\BreakTime;
use Illuminate\Support\Facades\Auth;

class MyAttendanceController extends Controller
{

    public function showClockIn(Request $request)
    {

        $currentDateTime = Carbon::now();
        // 現在の日時、曜日、時刻を取得
        $date = $currentDateTime->format('Y-m-d'); // 例: 2025年8月6日
        $dayOfWeek = $currentDateTime->format('D'); // 例: (水)
        $time = $currentDateTime->format('H:i:s'); // 時刻秒なし (例: 11:26)

        return view('/staff/clock_in', [
            'date' => $date,
            'dayOfWeek' => $dayOfWeek,
            'time' => $time,
        ]);
    }
    public function putClockIn(Request $request)
    {
        // 本日の日時を取得
        $today = Carbon::now()->format('Y-m-d');
        //$today = "2025-9-29"; //デバッグ用
        $todayOriginal = $today;

        // $todayOriginalの曜日取得
        //Carbonインスタンスに変換
        $todayCarbon = Carbon::createFromFormat('Y-m-d', $todayOriginal);
        // 曜日を取得
        $dayOfWeekOriginal = $todayCarbon->format('D');

        $user = Auth::id(); //本番ではAuth::id();となる

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

                $lastDateCarbon = Carbon::createFromFormat('Y-m-d', $last_date);

                // $start_day: $last_date の1日後
                $start_day = $lastDateCarbon->addDay()->format('Y-m-d');

                // $yesterday: $today の1日前
                $todayCarbon = Carbon::createFromFormat('Y-m-d', $today);
                $yesterday = $todayCarbon->subDay()->format('Y-m-d');

                // $start_day と $yesterday を Carbonインスタンスに変換
                $startDate = Carbon::createFromFormat('Y-m-d', $start_day);
                $endDate = Carbon::createFromFormat('Y-m-d', $yesterday);

                //dd($start_day." ". $yesterday);
                // 1日ごとにレコード追加
                while ($startDate->lte($endDate)) {
                    // 現在の日付をフォーマット（0なし表示に）
                    $currentDate = $startDate->format('Y-m-d');
                    // 曜日を計算 (例: "(月)")
                    $dayOfWeek = $startDate->format('D');
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
                'day_of_week' => $dayOfWeekOriginal,
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

            $message = "本日は退勤済みです。";
            return view('/staff/temporary_message', [
                'message' => '本日は退勤済みです。',
            ]);

            /*
            $currentDateTime = Carbon::now(); //job_start時間取得用
            // 新しいレコードを作成
            Job::create([
                'user_id' => $user,
                'date' => $todayOriginal,
                'day_of_week' => $dayOfWeekOriginal,
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
        $auth_id = Auth::id(); //本番はAuth::id();

        $currentDateTime = session('currentDateTime');
        //dd($currentDateTime);
        if ($currentDateTime ==  null) {
            $currentDateTime = Carbon::now();
        }
        $thisYearMonth = $currentDateTime->format('Y-m');

        $jobs = Job::with('breakTime')
            ->where('user_id', $auth_id) //本番はAuth::id();
            ->where('date', 'LIKE', $thisYearMonth . '%')
            ->get();
        //dd($jobs);
        foreach ($jobs as $job) {
            //            if ($job->break_duration == 0 || $job->job_duration == 0) {
            //dd($job);
            $breakDuration = 0;
            foreach ($job->breakTime as $break) {

                //$breakTime = BreakTime::find($break->id);
                $breakDuration += $break->calculateDuration();
            }
            $job = Job::find($job->id);
            $jobDuration = $job->calculateDuration() - $breakDuration;

            $job->break_duration = $breakDuration;
            $job->job_duration = $jobDuration;
            //dd($jobDuration . " " . $breakDuration);
            $job->save();

            // 最新のデータを再取得 viewの表示遅れ防止
            $jobs = Job::with('breakTime')
                ->where('user_id', $auth_id)//本番はAuth::id();
                ->where('date', 'LIKE', $thisYearMonth . '%')
                ->get();
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
