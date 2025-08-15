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
        //dd($currentDateTime);
        if ($currentDateTime ==  null) {
            $currentDateTime = Carbon::now();
        }
        $date = Carbon::parse($currentDateTime)->format('Y-m-d');

        //$date = $currentDateTime->format('Y-m');
        //dd($date);
        $jobs = Job::where('date', $date)->with('user')->get();
        $users = User::with('job')->get();
        //dd($date);
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
    public function getStaffs(){
        $staffs=User::all();
        return view('admin.staffs',[
            'staffs' => $staffs,
        ]);
    }
}
