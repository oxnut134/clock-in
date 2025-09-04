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

class AdminOperateAttendanceDetailTest extends TestCase
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

        $user->save();

        //------------ Jobs and breaks(BreakTime)  ----------------------

        $users = User::all();
        for ($i = 0; $i < 100; $i++) { // X日分のデータを生成
            foreach ($users as $user) {
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

    public function testSelectedDataShownInDetailPage()
    {
        //===========     test 13-1   ============/

        $response = $this->post('/admin/login', [
            'email' => 'Admin1@test.com',   //管理者
            'password' => 'abc12345',
        ]);
        $response = $this->get('/admin/attendances');
        $today = carbon::now()->format('Y-m-d');
        $job = Job::with(['user', 'breakTime'])
            ->where('date', $today)
            ->inRandomOrder() //randomに並べ替え
            ->first();
        //詳細画面表示
        $response = $this->get('/admin/attendances/' . $job->id);
        //表示確認
        $response->assertSee($job->user->name);
        $response->assertSee(Carbon::parse($job->date)->format('Y年'));
        $response->assertSee(Carbon::parse($job->date)->format('n月j日'));
        $response->assertSee(Carbon::parse($job->job_start)->format('H:i'));
        $response->assertSee(Carbon::parse($job->job_finish)->format('H:i'));
        foreach ($job->breakTime as $breakTime) {
            $response->assertSee(Carbon::parse($breakTime->break_start)->format('H:i'));
            $response->assertSee(Carbon::parse($breakTime->break_finish)->format('H:i'));
        }
        $response->assertSee($job->remark);
    }
    public function testJobStartAfterJobFinishValidation()
    {
        //===========     test 13-2   ============/

        $response = $this->post('/admin/login', [
            'email' => 'Admin1@test.com',   //管理者
            'password' => 'abc12345',
        ]);
        $response = $this->get('/admin/attendances');
        $today = carbon::now()->format('Y-m-d');
        $job = Job::with(['user', 'breakTime'])
            ->where('date', $today)
            ->inRandomOrder() //randomに並べ替え
            ->first();
        //詳細画面表示
        $response = $this->get('/admin/attendances/' . $job->id);
        // データを配列に変換
        foreach ($job->breakTime as $break) {
            $array = [
                'job_start' => Carbon::parse($job->job_finish)->addMinutes(30)->format('H:i'), // 開始時間
                'job_finish' => Carbon::parse($job->job_finish)->format('H:i'), // 終了時間
                'breakTimes' => [], // ブレイクタイムの初期化
                'remark' => "test", // 備考
                'id' => $job->id, // ジョブID
            ];
            $array['breakTimes'][] = [
                'id' => $break->id,
                'break_start' => Carbon::parse($break->break_start)->format('H:i'),
                'break_finish' => Carbon::parse($break->break_finish)->format('H:i'),
            ];
        }
        $response = $this->post('/admin/apply', $array);
        $response = $this->get('/admin/attendances/' . $job->id);

        $response->assertSee("出勤時間もしくは退勤時間が不適切な時刻です");
    }
    public function testBreakStartAfterJobFinishValidation()
    {
        //===========     test 13-3   ============/

        $response = $this->post('/admin/login', [
            'email' => 'Admin1@test.com',   //管理者
            'password' => 'abc12345',
        ]);
        $response = $this->get('/admin/attendances');
        $today = carbon::now()->format('Y-m-d');
        $job = Job::with(['user', 'breakTime'])
            ->where('date', $today)
            ->inRandomOrder() //randomに並べ替え
            ->first();
        //詳細画面表示
        $response = $this->get('/admin/attendances/' . $job->id);
        // データを配列に変換
        foreach ($job->breakTime as $break) {
            $array = [
                'job_start' => Carbon::parse($job->job_start)->format('H:i'), // 開始時間
                'job_finish' => Carbon::parse($job->job_finish)->format('H:i'), // 終了時間
                'breakTimes' => [], // ブレイクタイムの初期化
                'remark' => "test", // 備考
                'id' => $job->id, // ジョブID
            ];
            $array['breakTimes'][] = [
                'id' => $break->id,
                'break_start' => Carbon::parse($job->job_finish)->addMinutes(30)->format('H:i'),
                'break_finish' => Carbon::parse($break->break_finish)->format('H:i'),
            ];
        }
        $response = $this->post('/admin/apply', $array);
        $response = $this->get('/admin/attendances/' . $job->id);

        $response->assertSee("休憩時間が不適切な時刻です");
    }
    public function testBreakFinishAfterJobFinishValidation()
    {
        //===========     test 13-4   ============/

        $response = $this->post('/admin/login', [
            'email' => 'Admin1@test.com',   //管理者
            'password' => 'abc12345',
        ]);
        $response = $this->get('/admin/attendances');
        $today = carbon::now()->format('Y-m-d');
        $job = Job::with(['user', 'breakTime'])
            ->where('date', $today)
            ->inRandomOrder() //randomに並べ替え
            ->first();
        //詳細画面表示
        $response = $this->get('/admin/attendances/' . $job->id);
        // データを配列に変換
        foreach ($job->breakTime as $break) {
            $array = [
                'job_start' => Carbon::parse($job->job_start)->format('H:i'), // 開始時間
                'job_finish' => Carbon::parse($job->job_finish)->format('H:i'), // 終了時間
                'breakTimes' => [], // ブレイクタイムの初期化
                'remark' => "test", // 備考
                'id' => $job->id, // ジョブID
            ];
            $array['breakTimes'][] = [
                'id' => $break->id,
                'break_start' => Carbon::parse($break->break_start)->format('H:i'),
                'break_finish' => Carbon::parse($job->job_finish)->addMinutes(30)->format('H:i'),
            ];
        }
        $response = $this->post('/admin/apply', $array);
        $response = $this->get('/admin/attendances/' . $job->id);

        $response->assertSee("休憩時間もしくは退勤時間が不適切な時刻です");
    }
    public function testNotInputRemarkValidation()
    {
        //===========     test 13-5   ============/

        $response = $this->post('/admin/login', [
            'email' => 'Admin1@test.com',   //管理者
            'password' => 'abc12345',
        ]);
        $response = $this->get('/admin/attendances');
        $today = carbon::now()->format('Y-m-d');
        $job = Job::with(['user', 'breakTime'])
            ->where('date', $today)
            ->inRandomOrder() //randomに並べ替え
            ->first();
        //詳細画面表示
        $response = $this->get('/admin/attendances/' . $job->id);
        // データを配列に変換
        foreach ($job->breakTime as $break) {
            $array = [
                'job_start' => Carbon::parse($job->job_start)->format('H:i'), // 開始時間
                'job_finish' => Carbon::parse($job->job_finish)->format('H:i'), // 終了時間
                'breakTimes' => [], // ブレイクタイムの初期化
                'remark' => "", // 備考
                'id' => $job->id, // ジョブID
            ];
            $array['breakTimes'][] = [
                'id' => $break->id,
                'break_start' => Carbon::parse($break->break_start)->format('H:i'),
                'break_finish' => Carbon::parse($break->break_finish)->format('H:i'),
            ];
        }
        $response = $this->post('/admin/apply', $array);
        $response = $this->get('/admin/attendances/' . $job->id);

        $response->assertSee("備考を記入してください");
    }
}
