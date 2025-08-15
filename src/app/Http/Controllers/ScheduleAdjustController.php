<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Job;
use App\Models\BreakTime;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;



class ScheduleAdjustController extends Controller
{
    public function getMyDetail($id)
    {
        $authId = Auth::id(); //本番っではAuth::id()となる
        $name = User::find($authId)->name;
        $job = Job::where('id', $id)->first();
        $date = $job->date;
        $jobStart = $job->job_start;
        $jobFinish = $job->job_finish;
        $remark = $job->remark;

        $breakTimes = $job->breakTime;

        //dd($job, $breakTimes);
        return view('/staff/my_detail', [
            'name' => $name,
            'job' => $job,
            'date' => $date,
            'job_start' => $jobStart,
            'job_finish' => $jobFinish,
            'remark' => $remark,
            'breakTimes' => $breakTimes,
            'job_id' => $job->id,
        ]);
    }
    public function applyNewSchedule(Request $request)
    {
        //dd($request);

        //idでjobインスタンス取得
        $job = Job::where('id', $request->id)->first();
        $job->job_start = Carbon::createFromFormat('H:i:s', $job->job_start)->format('H:i');
        $job->job_finish = Carbon::createFromFormat('H:i:s', $job->job_finish)->format('H:i');

        // 更新前データ取得
        $originalValuesJob = [
            'job_start' => $job->job_start,
            'job_finish' => $job->job_finish,
            'remark' => $job->remark,
        ];

        // テーブル保存
        $job->updateStart($request->job_start);
        $job->updateFinish($request->job_finish);
        $job->updateRemark($request->remark);

        // 更新後データ取得
        $newValuesJob = [
            'job_start' => $request->job_start,
            'job_finish' => $request->job_finish,
            'remark' => $request->remark,
        ];

        // 変更チェック
        $hasChanges = false;
        foreach ($originalValuesJob as $field => $oldValue) {
            if ($oldValue != $newValuesJob[$field]) {
                $hasChanges = true;
                break; // 1つでも変更があればループを抜ける
            }
        }

        // 変更あればjob_statusをapplied
        if ($hasChanges && $job->job_status != "approved") {
            //$job->update(array_merge($newValuesJob, ['job_status' => 'applied']));
            $job->updateStatus("applied");
        }



        $breakTimes = $request->breakTimes;
        foreach ($breakTimes as $breakTimeTemp) {

            //idでbreakTimeインスタンス取得
            $breakTime = BreakTime::where('id', $breakTimeTemp['break_id'])->first();



            // 更新前データ取得
            $originalValues = [
                'break_start' => $breakTime->break_start,
                'break_finish' => $breakTime->break_finish,
            ];


            // テーブル保存
            $breakTime->updateStart($breakTimeTemp['break_start']);
            $breakTime->updateFinish($breakTimeTemp['break_finish']);


            // 更新後データ取得
            $newValues = [
                'break_start' => $breakTimeTemp['break_start'],
                'break_finish' => $breakTimeTemp['break_finish'],
            ];

            // 変更チェック
            $hasChanges = false;
            foreach ($originalValues as $field => $oldValue) {
                if ($oldValue != $newValues[$field]) {
                    $hasChanges = true;
                    break; // 1つでも変更があればループを抜ける
                }
            }

            // 変更あればbreak_statusをapplied
            if ($hasChanges && $job->break_status != "approved") {
                //$breakTime->update(array_merge($newValues, ['break_status' => 'applied']));
                $job->updateStatus("applied");
            }
        }
        return redirect()->route('attendance.list');
    }
    public function getMytApplyList($param)
    {
        $auth_id = Auth::id();
        $user = User::find($auth_id);

        switch ($param) {
            case 'applied':
                $appliedJobs = Job::where('user_id', $auth_id)
                    ->where('job_status', "applied")->get();
                return view('/staff/my_applies', [
                    'jobs' => $appliedJobs,
                    'user' => $user,
                    'status' => "承認待ち"
                ]);
            case 'approved':
                $approvedJobs = Job::where('user_id', $auth_id)
                    ->where('job_status', "approved")->get();
                return view('/staff/my_applies', [
                    'jobs' => $approvedJobs,
                    'user' => $user,
                    'status' => "承認済み"
                ]);

            default:
                $appliedJobs = Job::where('user_id', $auth_id)
                    ->where('job_status', "applied")->get();
                return view('/staff/my_applies', [
                    'jobs' => $appliedJobs,
                    'user' => $user,
                    'status' => "承認待ち"
                ]);
        }
    }
}
