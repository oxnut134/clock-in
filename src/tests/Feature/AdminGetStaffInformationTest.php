<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Admin;
use App\Models\User;
use App\Models\Job;
use App\Models\BreakTime;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AdminGetStaffInformationTest extends TestCase
{
    /*==
     = A basic test example.
     =
     = @return void
     =*/
    use RefreshDatabase; // データベースをリフレッシュするトレイトを使用

    protected function setUp(): void
    {
        parent::setUp();
        // テーブルデータ作成
        //---- Admins -------
        $admins = [];
        $admin = Admin::create([
            'name' => 'Admin1', //勤務外
            'email' => 'Admin1@test.com',
            'password' => bcrypt('abc12345'),
            'password_confirmation' => bcrypt('abc12345'),
            'email_verified_at' => now(),
        ]);

        //---- User -------
        $users = [];
        $user = User::create([
            'name' => 'User1', //勤務外
            'email' => 'User1@test.com',
            'password' => bcrypt('abc12345'),
            'password_confirmation' => bcrypt('abc12345'),
            'email_verified_at' => now(),
        ]);

        $user = User::create([
            'name' => 'User2', //出勤中
            'email' => 'User2@test.com',
            'password' => bcrypt('abc12345'),
            'password_confirmation' => bcrypt('abc12345'),
            'email_verified_at' => now(),

        ]);
        $user = User::create([
            'name' => 'User3', //休憩中
            'email' => 'User3@test.com',
            'password' => bcrypt('abc12345'),
            'password_confirmation' => bcrypt('abc12345'),
            'email_verified_at' => now(),

        ]);
        $user = User::create([
            'name' => 'User4', //出勤中
            'email' => 'User4@test.com',
            'password' => bcrypt('abc12345'),
            'password_confirmation' => bcrypt('abc12345'),
            'email_verified_at' => now(),

        ]);
        $user = User::create([
            'name' => 'User5', //退勤済
            'email' => 'User5@test.com',
            'password' => bcrypt('abc12345'),
            'password_confirmation' => bcrypt('abc12345'),
            'email_verified_at' => now(),

        ]);
        /*        $users = User::all();
        foreach ($users as $user) {
            for ($i = 0; $i < 100; $i++) { // X日分のデータを生成
                $date = Carbon::create(Carbon::now()->year, 7, 1)->addDays($i); // 現在の年のm月d日からの日付で取得
                DB::table('jobs')->insert([
                    'user_id' => $user->id, //rand(1, 4),\\\
                    'date' => $date->format('Y-m-d'),
                    'day_of_week' => $date->format('D'),
                    'job_start' => Carbon::createFromTime(rand(8, 8), rand(30, 59))->format('H:i:s'), // ランダムな出勤時間
                    'job_finish' => Carbon::createFromTime(rand(18, 18), rand(0, 30))->format('H:i:s'), // ランダムな退勤時間
                    'job_status' => "normal",
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            $jobs = Job::where('user_id', $user->id)->get();
            foreach ($jobs as $job) { //Jobのレコード数分繰り返し
                DB::table('breaks')->insert([
                    'job_id' => $job->id, //+ 1,
                    'break_start' => Carbon::createFromTime(12, rand(0, 0))->format('H:i:s'), // ランダムな出勤時間
                    'break_finish' => Carbon::createFromTime(13, rand(0, 0))->format('H:i:s'), // ランダムな退勤時間
                    'break_status' => "normal",
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $r = rand(1, 5); //5回に1回2レコード目のbreakを生成
                if ($r == 6) {               //if内実行せず（２回目の休憩レコードなしで固定）
                    DB::table('breaks')->insert([
                        'job_id' => $job->id,
                        'break_start' => Carbon::createFromTime(13, rand(0, 0))->format('H:i:s'), // ランダムな出勤時間
                        'break_finish' => Carbon::createFromTime(13, rand(30, 30))->format('H:i:s'), // ランダムな退勤時間
                        'break_status' => "normal",
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }*/
        $users = User::all();
        foreach ($users as $user) {
            for ($i = 0; $i < 100; $i++) { // X日分のデータを生成
                $random = Rand(1, 10);
                switch ($random) {
                    case 2:
                        $job_status = "applied";
                        break;
                    case 4:
                        $job_status = "approved";
                        break;
                    default:
                        $job_status = "normal";
                        break;
                }
                $date = Carbon::create(Carbon::now()->year, 7, 1)->addDays($i); // 現在の年のm月d日からの日付で取得
                if ($date->format('D') != "Sun") {
                    DB::table('jobs')->insert([
                        'user_id' => $user->id, //rand(1, 4),\\\
                        'date' => $date->format('Y-m-d'),
                        'day_of_week' => $date->format('D'),
                        'job_start' => Carbon::createFromTime(rand(8, 8), rand(30, 59))->format('H:i:s'), // ランダムな出勤時間
                        'job_finish' => Carbon::createFromTime(rand(18, 18), rand(0, 30))->format('H:i:s'), // ランダムな退勤時間
                        'job_status' => $job_status,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    DB::table('jobs')->insert([
                        'user_id' => $user->id, //rand(1, 4),\\\
                        'date' => $date->format('Y-m-d'),
                        'day_of_week' => $date->format('D'),
                        'job_status' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
            $jobs = Job::where('user_id', $user->id)->get();
            foreach ($jobs as $job) { //Jobのレコード数分繰り返し
                if ($job->job_start != null) {
                    DB::table('breaks')->insert([
                        'job_id' => $job->id, //+ 1,
                        'break_start' => Carbon::createFromTime(12, rand(0, 0))->format('H:i:s'), // ランダムな出勤時間
                        'break_finish' => Carbon::createFromTime(13, rand(0, 0))->format('H:i:s'), // ランダムな退勤時間
                        'break_status' => "normal",
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $r = rand(1, 5); //5回に1回2レコード目のbreakを生成
                    if ($r == 6) {               //if内実行せず（２回目の休憩レコードなしで固定）
                        DB::table('breaks')->insert([
                            'job_id' => $job->id,
                            'break_start' => Carbon::createFromTime(13, rand(0, 0))->format('H:i:s'), // ランダムな出勤時間
                            'break_finish' => Carbon::createFromTime(13, rand(30, 30))->format('H:i:s'), // ランダムな退勤時間
                            'break_status' => "normal",
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }
    }

    public function testConfirmAllStaffsNamesAndEmails()
    {
        //===========     test 14-1   ============/

        $response = $this->post('/admin/login', [
            'email' => 'Admin1@test.com',   //管理者
            'password' => 'abc12345',
        ]);
        $response = $this->get('/admin/attendances');
        $response = $this->get('/admin/users');
        $users = User::all();
        foreach ($users as $user) {
            $response->assertSee($user->name);
            $response->assertSee($user->email);
        }
    }
    public function testStaffsAttendanceDisplayedCorrectly()
    {
        //===========     test 14-2   ============/

        $response = $this->post('/admin/login', [
            'email' => 'Admin1@test.com',   //管理者
            'password' => 'abc12345',
        ]);
        $response = $this->get('/admin/attendances');
        $response = $this->get('/admin/users');
        $user = User::query()->inRandomOrder() //randomに並べ替え
            ->first();
        $response = $this->get('/admin/users/' . $user->id . '/attendances');

        $response->assertSee($user->name . ' さんの勤怠');


        $thisYearMonth = carbon::now()->format('Y-m');
        $response->assertSee($thisYearMonth);

        $jobs = Job::with('breakTime')
            ->where('user_id', Auth::id()) //本番はAuth::id();
            ->where('date', 'LIKE', $thisYearMonth . '%')
            ->get();

        foreach ($jobs as $job) {
            $response->assertSee(Carbon::parse($job->date)->format('m/d'));
            $response->assertSee(Carbon::parse($job->job_start)->format('H:i'));
            $response->assertSee(Carbon::parse($job->job_finish)->format('H:i'));
            $breaks = BreakTime::where('job_id', $job->id)->get();
            $breakDuration = 0;
            foreach ($breaks as $break) {
                $breakDuration += $break->calculateDuration();
            }
            //break duration 確認
            $breakHours = floor($breakDuration / 60); // 時間
            $breakMinutes = $breakDuration % 60; // 分
            // hh:mm形式
            $breakTime = sprintf('%d:%02d', $breakHours, $breakMinutes);

            $response->assertSee($breakTime); //確認

            //job duration 確認
            $jobDuration = $job->calculateDuration() - $breakDuration;
            if ($jobDuration < 0) {
                $jobDuration = 0;
            }
            $jobHours = floor($jobDuration / 60); // 時間
            $jobMinutes = $jobDuration % 60; // 分
            // hh:mm形式
            $jobTime = sprintf('%d:%02d', $jobHours, $jobMinutes);

            $response->assertSee($jobTime); //確認

        }
    }
    public function testLastMonthStaffAttendanceDisplayed()
    {
        //===========     test 14-3   ============/

        $response = $this->post('/admin/login', [
            'email' => 'Admin1@test.com',   //管理者
            'password' => 'abc12345',
        ]);
        $response = $this->get('/admin/attendances');
        $response = $this->get('/admin/users');
        $user = User::query()->inRandomOrder() //randomに並べ替え
            ->first();
        $response = $this->get('/admin/users/' . $user->id . '/attendances');
        $response = $this->post('/admin/users/attendances/last_month', [
            'user' =>  $user,
        ]);
        $response = $this->get('/admin/users/' . $user->id . '/attendances');

        $response->assertSee($user->name . ' さんの勤怠');


        $thisYearMonth = carbon::now()->subMonth()->format('Y-m');
        $response->assertSee($thisYearMonth);

        $jobs = Job::with('breakTime')
            ->where('user_id', Auth::id()) //本番はAuth::id();
            ->where('date', 'LIKE', $thisYearMonth . '%')
            ->get();

        foreach ($jobs as $job) {
            $response->assertSee(Carbon::parse($job->date)->format('m/d'));
            $response->assertSee(Carbon::parse($job->job_start)->format('H:i'));
            $response->assertSee(Carbon::parse($job->job_finish)->format('H:i'));
            $breaks = BreakTime::where('job_id', $job->id)->get();
            $breakDuration = 0;
            foreach ($breaks as $break) {
                $breakDuration += $break->calculateDuration();
            }
            //break duration 確認
            $breakHours = floor($breakDuration / 60); // 時間
            $breakMinutes = $breakDuration % 60; // 分
            // hh:mm形式
            $breakTime = sprintf('%d:%02d', $breakHours, $breakMinutes);

            $response->assertSee($breakTime); //確認

            //job duration 確認
            $jobDuration = $job->calculateDuration() - $breakDuration;
            if ($jobDuration < 0) {
                $jobDuration = 0;
            }
            $jobHours = floor($jobDuration / 60); // 時間
            $jobMinutes = $jobDuration % 60; // 分
            // hh:mm形式
            $jobTime = sprintf('%d:%02d', $jobHours, $jobMinutes);

            $response->assertSee($jobTime); //確認

        }
    }
    public function testNextMonthStaffAttendanceDisplayed()
    {
        //===========     test 14-4   ============/

        $response = $this->post('/admin/login', [
            'email' => 'Admin1@test.com',   //管理者
            'password' => 'abc12345',
        ]);
        $response = $this->get('/admin/attendances');
        $response = $this->get('/admin/users');
        $user = User::query()->inRandomOrder() //randomに並べ替え
            ->first();
        $response = $this->get('/admin/users/' . $user->id . '/attendances');
        $response = $this->post('/admin/users/attendances/next_month', [
            'user' =>  $user,
        ]);
        $response = $this->get('/admin/users/' . $user->id . '/attendances');

        $response->assertSee($user->name . ' さんの勤怠');


        $thisYearMonth = carbon::now()->addMonth()->format('Y-m');
        $response->assertSee($thisYearMonth);

        $jobs = Job::with('breakTime')
            ->where('user_id', Auth::id()) //本番はAuth::id();
            ->where('date', 'LIKE', $thisYearMonth . '%')
            ->get();

        foreach ($jobs as $job) {
            $response->assertSee(Carbon::parse($job->date)->format('m/d'));
            $response->assertSee(Carbon::parse($job->job_start)->format('H:i'));
            $response->assertSee(Carbon::parse($job->job_finish)->format('H:i'));
            $breaks = BreakTime::where('job_id', $job->id)->get();
            $breakDuration = 0;
            foreach ($breaks as $break) {
                $breakDuration += $break->calculateDuration();
            }
            //break duration 確認
            $breakHours = floor($breakDuration / 60); // 時間
            $breakMinutes = $breakDuration % 60; // 分
            // hh:mm形式
            $breakTime = sprintf('%d:%02d', $breakHours, $breakMinutes);

            $response->assertSee($breakTime); //確認

            //job duration 確認
            $jobDuration = $job->calculateDuration() - $breakDuration;
            if ($jobDuration < 0) {
                $jobDuration = 0;
            }
            $jobHours = floor($jobDuration / 60); // 時間
            $jobMinutes = $jobDuration % 60; // 分
            // hh:mm形式
            $jobTime = sprintf('%d:%02d', $jobHours, $jobMinutes);

            $response->assertSee($jobTime); //確認

        }
    }
    public function testTransitionToAttendanceDetailOfTheDay()
    {
        //===========     test 14-5   ============/

        $response = $this->post('/admin/login', [
            'email' => 'Admin1@test.com',   //管理者
            'password' => 'abc12345',
        ]);
        $response = $this->get('/admin/attendances');
        $response = $this->get('/admin/users');
        $user = User::query()->inRandomOrder() //randomに並べ替え
            ->first();
        $response = $this->get('/admin/users/' . $user->id . '/attendances');

        $response->assertSee($user->name . ' さんの勤怠');

        $today = carbon::now()->format('Y-m-d');
        $thisYearMonth = carbon::now()->format('Y-m');
        $response->assertSee($thisYearMonth);

        $job = Job::with('breakTime')
            ->where('user_id', $user->id)
            ->where('date', 'LIKE', $thisYearMonth . '%')
            ->inRandomOrder() //randomに並べ替え
            ->first();

        $response = $this->get('/admin/attendances/' . $job->id);

        $response->assertSee('勤怠詳細');
        $response->assertSee($job->user->name);


        $response->assertSee(Carbon::parse($job->date)->format('Y年'));
        $response->assertSee(Carbon::parse($job->date)->format('n月j日'));
        $response->assertSee(Carbon::parse($job->job_start)->format('H:i'));
        $response->assertSee(Carbon::parse($job->job_finish)->format('H:i'));
        foreach ($job->breakTime as $break) {
            $response->assertSee(Carbon::parse($break->break_start)->format('H:i'));
            $response->assertSee(Carbon::parse($break->break_finish)->format('H:i'));
        }
    }
}
