<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Job;
use App\Models\BreakTime;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Http\Requests\AdminStaffDetailRequest;


class AdminScheduleAdjustController extends Controller
{
    public function getTodaysStaffDetail($id)
    {
        $job = Job::find($id);
        $userId = $job->user_id; //パラメータより取得

        $user = User::find($userId);
        $name = $user->name;
        $date = $job->date;
        $job = Job::where('date', $date)
            ->where('user_id', $userId)
            ->first();
        $date = $job->date;
        $jobStart = $job->job_start;
        $jobFinish = $job->job_finish;
        $remark = $job->remark;

        $breakTimes = $job->breakTime;
        return view('/admin/todays_staff_detail', [
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
    public function applyNewSchedule(AdminStaffDetailRequest $request)
    {
        //idでjobインスタンス取得
        $job = Job::where('id', $request->id)->first();
        $job->job_start = Carbon::createFromFormat('H:i:s', $job->job_start)->format('H:i');
        if ($job->job_finish != null) {
            $job->job_finish = Carbon::createFromFormat('H:i:s',  $job->job_finish)->format('H:i');
        } else {
            $job->job_finish = null;
        }
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

        // 変更あればjob_statusをapplied,apply_dateを本日
        if ($hasChanges ) {
            $job->updateStatus("applied");
            $job->apply_date =Carbon::now()->format('Y-m-d');
            $job->save();
        }

        $breakTimes = $request->breakTimes;
        if ($breakTimes != null) {
            foreach ($breakTimes as $breakTimeTemp) {

                //idでbreakTimeインスタンス取得
                $breakTime = BreakTime::where('id', $breakTimeTemp['break_id'])->first();
                $breakTime->break_start = Carbon::createFromFormat('H:i:s', $breakTime->break_start)->format('H:i');
                $breakTime->break_finish = Carbon::createFromFormat('H:i:s', $breakTime->break_finish)->format('H:i');



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

                    $job->updateStatus("applied");
                }
            }
        }
        return redirect()->route('admin.attendances');
    }
    public function getStaffApplyList(Request $request)
    {
       $param = $request->query('param');
        switch ($param) {
            case 'applied':
                $appliedJobs = Job::where('job_status', "applied")
                    ->with('user')->get();
                return view('/admin/staff_applies', [
                    'jobs' => $appliedJobs,
                    'status' => "承認待ち"
                ]);
            case 'approved':
                $approvedJobs = Job::where('job_status', "approved")
                    ->with('user')->get();
                return view('/admin/staff_applies', [
                    'jobs' => $approvedJobs,
                    'status' => "承認済み"
                ]);

            default:
                $appliedJobs = Job::where('job_status', "dummy")
                    ->with('user')->get();
                return view('/admin/staff_applies', [
                    'jobs' => $appliedJobs,
                    'status' => "承認待ち"
                ]);
        }
    }
    public function showStaffApplyDetail($id)
    {

        $job = Job::find($id);
        $userId = $job->user_id; //パラメータより取得

        $user = User::find($userId);
        $name = $user->name;
        $date = $job->date;
        $job = Job::where('date', $date)
            ->where('user_id', $userId)
            ->first();
        $date = $job->date;
        $jobStart = $job->job_start;
        $jobFinish = $job->job_finish;
        $remark = $job->remark;

        $breakTimes = $job->breakTime;

        return view('/admin/approve', [
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
    public function approveNewSchedule(Request $request)
    {

        $job = Job::find($request->job_id);
        $job->updateStatus("approved");

        return redirect()->route('admin.requests', ['param' => 'approved']);
    }
}
