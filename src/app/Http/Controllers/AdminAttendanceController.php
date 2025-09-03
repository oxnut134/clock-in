<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Job;
use App\Models\BreakTime;
use Illuminate\Support\Facades\Auth;

class AdminAttendanceController extends Controller
{
    public function getTodaysStaff()
    {
        $currentDateTime = session('date');
        if ($currentDateTime ==  null) {
            $currentDateTime = Carbon::now();
        }
        $date = Carbon::parse($currentDateTime)->format('Y-m-d');

        $jobs = Job::where('date', $date)->with('user')->get();
        $users = User::with('job')->get();

        foreach ($jobs as $job) {
            $breakDuration = 0;
            foreach ($job->breakTime as $break) {

                $breakDuration += $break->calculateDuration();
            }
            $job = Job::find($job->id);
            $jobDuration = $job->calculateDuration() - $breakDuration;
            if ($jobDuration < 0) {
                $jobDuration = 0;
            }

            $job->break_duration = $breakDuration;
            $job->job_duration = $jobDuration;
            $job->save();

            $jobs = Job::where('date', $date)->with('user')->get(); // 最新のデータを再取得 viewの表示遅れ防止

        }

        return view('admin/todays_staffs', [
            'date' => $date,
            'jobs' => $jobs,
            'users' => $users,
        ]);
    }

    public function getYesterdaysStaff(Request $request)
    {
        $date = Carbon::parse($request->date)->subDay()->toDateString(); //前日
        return redirect()->route('admin.attendances')->with([
            'date' => $date,
        ]);
    }
    public function getTomorrowsStaff(Request $request)
    {
        $date = Carbon::parse($request->date)->addDay()->toDateString(); //翌日
        return redirect()->route('admin.attendances')->with([
            'date' => $date,
        ]);
    }
    public function getStaffs()
    {
        $staffs = User::all();
        return view('admin.staffs', [
            'staffs' => $staffs,
        ]);
    }
    public function getMonthlyStaffJobs($user)
    {
        $user_id = $user;
        $userName = User::find($user)->name;
        $currentDateTime = session('currentDateTime');
        if ($currentDateTime ==  null) {
            $currentDateTime = Carbon::now();
        }
        $thisYearMonth = $currentDateTime->format('Y-m');

        $jobs = Job::with('breakTime')
            ->where('user_id', $user_id)
            ->where('date', 'LIKE', $thisYearMonth . '%')
            ->get();
        foreach ($jobs as $job) {
            $breakDuration = 0;
            foreach ($job->breakTime as $break) {
                $breakDuration += $break->calculateDuration();
            }
            $job = Job::find($job->id);
            $jobDuration = $job->calculateDuration() - $breakDuration;
            if ($jobDuration < 0) {
                $jobDuration = 0;
            }

            $job->break_duration = $breakDuration;
            $job->job_duration = $jobDuration;
            $job->save();

            // 最新のデータを再取得 viewの表示遅れ防止
            $jobs = Job::with('breakTime')
                ->where('user_id', $user_id) //本番はAuth::id();
                ->where('date', 'LIKE', $thisYearMonth . '%')
                ->get();
        }

        $thisYearMonth = $currentDateTime->format('Y-m');
        return view('/admin/staff_attendance', [
            'this_year_month' => $thisYearMonth,
            'jobs' => $jobs,
            'currentDateTime' => $currentDateTime,
            'user' => $user,
            'user_name' => $userName,
        ]);
    }
    public function ShowLastMonth(Request $request)
    {
        $user = $request->user;
        $currentDateTime = $request->currentDateTime;
        $lastMonth = Carbon::parse($currentDateTime)->subMonth(); //前月
        return redirect()->route('admin.users.attendance', ['user' => $user])
            ->with(['currentDateTime' => $lastMonth]);
    }
    public function ShowNextMonth(Request $request)
    {
        $user = $request->user;
        $currentDateTime = $request->currentDateTime;
        $nextMonth = Carbon::parse($currentDateTime)->addMonth(); //翌月
        return redirect()->route('admin.users.attendance', ['user' => $user])
            ->with(['currentDateTime' => $nextMonth]);
    }
    public function downloadCsv(Request $request)
    {
        $user = $request->user;
        $thisYearMonth = $request->thisYearMonth;
        // 勤怠データ取得
        $jobs = Job::with('breakTime')
            ->where('user_id', $user) //本番はAuth::id();
            ->where('date', 'LIKE', $thisYearMonth . '%')
            ->get();
        // CSVヘッダー
        $csvHeader = ['日付', '出勤', '退勤', '休憩', '合計'];

        // CSVデータ構築
        $csvData = [];
        foreach ($jobs as $job) {
            $csvData[] = [
                Carbon::parse($job->date)->format('Y/m/d'),
                $job->job_start ? Carbon::parse($job->job_start)->format('H:i') : '',
                $job->job_finish ? Carbon::parse($job->job_finish)->format('H:i') : '',
                floor($job->break_duration / 60) . ':' . str_pad($job->break_duration % 60, 2, '0', STR_PAD_LEFT),
                floor($job->job_duration / 60) . ':' . str_pad($job->job_duration % 60, 2, '0', STR_PAD_LEFT),
                //route('attendance.detail', ['id' => $job->id]),
            ];
        }

        // CSVファイル名
        $fileName = "attendance_{$user}_{$thisYearMonth}_" . now()->format('YmdHis') . ".csv";

        // HTTPヘッダーを設定
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
        ];

        // CSVデータ送信
        $callback = function () use ($csvHeader, $csvData) {
            $file = fopen('php://output', 'w');
            // ヘッダー行追加
            fputcsv($file, $csvHeader);

            // データ行追加
            foreach ($csvData as $row) {
                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
