<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Admin;
use App\Models\User;
use App\Models\Job;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminUpdateStaffAttendanceTest extends TestCase
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

    public function testAllAppliedAttendanceDisplayed()
    {
        //===========     test 15-1   ============/

        $response = $this->post('/admin/login', [
            'email' => 'Admin1@test.com',   //管理者
            'password' => 'abc12345',
        ]);
        $response = $this->get('/admin/attendances');
        $response = $this->get('/admin/requests?param=applied');
        $response->assertSee("申請一覧");
        $response->assertSee("承認待ち");


        $jobs = Job::with(['user', 'breakTime'])
            ->where('job_status', 'applied')
            ->get();

        foreach ($jobs as $job) {
            $response->assertSee($job->user->name);
            $response->assertSee(Carbon::parse($job->date)->format('Y/m/d'));
            $response->assertSee($job->remark);
            $response->assertSee(Carbon::parse($job->apply_date)->format('Y/m/d'));
        }
    }
    public function testAllApprovedAttendanceDisplayed()
    {
        //===========     test 15-2   ============/

        $response = $this->post('/admin/login', [
            'email' => 'Admin1@test.com',   //管理者
            'password' => 'abc12345',
        ]);
        $response = $this->get('/admin/attendances');
        $response = $this->get('/admin/requests?param=approved');
        $response->assertSee("申請一覧");
        $response->assertSee("承認済み");


        $jobs = Job::with(['user', 'breakTime'])
            ->where('job_status', 'approved')
            ->get();

        foreach ($jobs as $job) {
            $response->assertSee($job->user->name);
            $response->assertSee(Carbon::parse($job->date)->format('Y/m/d'));
            $response->assertSee($job->remark);
            $response->assertSee(Carbon::parse($job->apply_date)->format('Y/m/d'));
        }
    }
    public function testAppliedUpdateDetailDisplayedCorrectly()
    {
        //===========     test 15-3   ============/

        $response = $this->post('/admin/login', [
            'email' => 'Admin1@test.com',   //管理者
            'password' => 'abc12345',
        ]);
        $response = $this->get('/admin/attendances');
        $response = $this->get('/admin/requests?param=approved');
        $response->assertSee("申請一覧");
        $response->assertSee("承認済み");


        $job = Job::with(['user', 'breakTime'])
            ->where('job_status', 'approved')
            ->inRandomOrder()
            ->first();

        $response = $this->get('/admin/requests/' . $job->id);

        //確認
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
        $response->assertSee($job->remark);

        $response->assertSee('承認済み');
    }
    public function testApprovalOfAppliedUpdateExecutedCorrectly()
    {
        //===========     test 15-4   ============/

        $response = $this->post('/admin/login', [
            'email' => 'Admin1@test.com',   //管理者
            'password' => 'abc12345',
        ]);
        $response = $this->get('/admin/attendances');
        $response = $this->get('/admin/requests?param=applied');
        $response->assertSee("申請一覧");
        $response->assertSee("承認待ち");


        $job = Job::with(['user', 'breakTime'])
            ->where('job_status', 'approved')
            ->inRandomOrder()
            ->first();

        $response = $this->get('/admin/requests' . $job->id);

        $response = $this->post('/approve', [
            'job_id' => $job->id
        ]);
        $response = $this->get('/admin/requests?param=approved');

        $response->assertSee($job->name);
        $response->assertSee(Carbon::parse($job->date)->format('Y/m/d'));
        $response->assertSee($job->remark);
    }
}
