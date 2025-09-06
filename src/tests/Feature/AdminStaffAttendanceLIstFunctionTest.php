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


class AdminStaffAttendanceLIstFunctionTest extends TestCase
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
        $admin = [];
        $admin = Admin::create([
            'name' => 'Admin1', //勤務外
            'email' => 'Admin1@test.com',
            'password' => bcrypt('abc12345'),
        ]);
        $admin->save();

        //---- User -------
        $users = [];
        $user = User::create([
            'name' => 'テスト一郎', //勤務外
            'email' => 'test1@test.com',
            'password' => bcrypt('abc12345'),
            'password_confirmation' => bcrypt('abc12345'),
            'email_verified_at' => now(),
        ]);

        $user = User::create([
            'name' => 'テスト二郎', //出勤中
            'email' => 'test2@test.com',
            'password' => bcrypt('abc12345'),
            'password_confirmation' => bcrypt('abc12345'),
            'email_verified_at' => now(),

        ]);
        $user = User::create([
            'name' => 'テスト三郎', //休憩中
            'email' => 'test3@test.com',
            'password' => bcrypt('abc12345'),
            'password_confirmation' => bcrypt('abc12345'),
            'email_verified_at' => now(),

        ]);

        $user->save();

        //------------ Jobs and breaks(BreakTime)  ----------------------

        $users = User::all();
        for ($i = 0; $i < 100; $i++) { // X日分のデータを生成
            $date = Carbon::create(Carbon::now()->year, 7, 1)->addDays($i); // 現在の年のm月d日からの日付で取得
            foreach ($users as $user) {
                $random = Rand(1, 10);
                switch ($random) {
                    case 2:
                        $job_status = "applied";
                        $apply_date = $date;
                        break;
                    case 4:
                        $job_status = "approved";
                        $apply_date = $date;
                        break;
                    default:
                        $job_status = "normal";
                        $apply_date = null;
                        break;
                }
                if ($date->format('D') != "Sun") {
                    DB::table('jobs')->insert([
                        'user_id' => $user->id, //rand(1, 4),\\\
                        'date' => $date->format('Y-m-d'),
                        'day_of_week' => $date->format('D'),
                        'job_start' => Carbon::createFromTime(rand(8, 8), rand(30, 59))->format('H:i:s'), // ランダムな出勤時間
                        'job_finish' => Carbon::createFromTime(rand(18, 18), rand(0, 30))->format('H:i:s'), // ランダムな退勤時間
                        'job_status' => $job_status,
                        'apply_date' => $apply_date,
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
            $jobs = Job::where('date',  $date->format('Y-m-d'))->get();
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

    public function testTodaysAllStaffsAttendance()
    {
        //===========     test 12-1   ============/

        $response = $this->post('/admin/login', [
            'email' => 'Admin1@test.com',   //管理者
            'password' => 'abc12345',
        ]);
        $response = $this->get('/admin/attendances');

        $thisYearMonth = carbon::now()->format('Y-m');
        $today = carbon::now()->format('Y-m-d');
        //勤怠一覧画面確認
        $response->assertSee(Carbon::parse($today)->format('Y年n月j日') . " の勤怠");

        $jobs = Job::with(['user', 'breakTime'])
            ->where('date', $today)
            ->get();
        foreach ($jobs as $job) {
            $response->assertSee($job->user->name);
            if ($job->job_start != null) {
                $response->assertSee(Carbon::parse($job->job_start)->format('H:i'));
            }
            if ($job->job_finish != null) {
                $response->assertSee(Carbon::parse($job->job_finish)->format('H:i'));
            }

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
    public function testTodaysDateDisplayed()
    {
        //===========     test 12-2   ============/

        $response = $this->post('/admin/login', [
            'email' => 'Admin1@test.com',   //管理者
            'password' => 'abc12345',
        ]);
        $response = $this->get('/admin/attendances');

        $thisYearMonth = carbon::now()->format('Y-m');
        $today = carbon::now()->format('Y-m-d');

        //現在日付表示確認
        $response->assertSee(Carbon::parse($today)->format('Y年n月j日') . " の勤怠");

        $jobs = Job::with(['user', 'breakTime'])
            ->where('date', $today)
            ->get();
        foreach ($jobs as $job) {
            $response->assertSee($job->user->name);
            if ($job->job_start != null) {
                $response->assertSee(Carbon::parse($job->job_start)->format('H:i'));
            }
            if ($job->job_finish != null) {
                $response->assertSee(Carbon::parse($job->job_finish)->format('H:i'));
            }
        }
    }
    public function testPreviousDayPageDisplayed()
    {
        //===========     test 12-3   ============/

        $response = $this->post('/admin/login', [
            'email' => 'Admin1@test.com',   //管理者
            'password' => 'abc12345',
        ]);
        $response = $this->get('/admin/attendances');
        $response = $this->post('/admin/attendances/yesterday');
        $response = $this->get('/admin/attendances');

        $yesterday = carbon::now()->subDay()->format('Y-m-d');
        //昨日日付表示確認
        $response->assertSee(Carbon::parse($yesterday)->format('Y年n月j日') . " の勤怠");

        $jobs = Job::with(['user', 'breakTime'])
            ->where('date', $yesterday)
            ->get();
        foreach ($jobs as $job) {
            $response->assertSee($job->user->name);
            if ($job->job_start != null) {
                $response->assertSee(Carbon::parse($job->job_start)->format('H:i'));
            }
            if ($job->job_finish != null) {
                $response->assertSee(Carbon::parse($job->job_finish)->format('H:i'));
            }
        }
    }
    public function testNextDayPageDisplayed()
    {
        //===========     test 12-4   ============/

        $response = $this->post('/admin/login', [
            'email' => 'Admin1@test.com',   //管理者
            'password' => 'abc12345',
        ]);
        $response = $this->get('/admin/attendances');
        $response = $this->post('/admin/attendances/tomorrow');
        $response = $this->get('/admin/attendances');

        $tomorrow = carbon::now()->addDay()->format('Y-m-d');
        //昨日日付表示確認
        $response->assertSee(Carbon::parse($tomorrow)->format('Y年n月j日') . " の勤怠");

        $jobs = Job::with(['user', 'breakTime'])
            ->where('date', $tomorrow)
            ->get();
        foreach ($jobs as $job) {
            $response->assertSee($job->user->name);
            if ($job->job_start != null) {
                $response->assertSee(Carbon::parse($job->job_start)->format('H:i'));
            }
            if ($job->job_finish != null) {
                $response->assertSee(Carbon::parse($job->job_finish)->format('H:i'));
            }
        }
    }
}
