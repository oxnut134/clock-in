<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Admin;
use App\Models\Job;
use App\Models\BreakTime;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


class StaffMyDetailDisplayedFunctionTest extends TestCase
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
    public function test_my_name_displayed_at_my_detail()
    {
        //===========     test 10-1   ============/

        $response = $this->post('/login', [
            'email' => 'test1@test.com',   //勤怠登録済ユーザー
            'password' => 'abc12345',
        ]);
        $response = $this->get('/');
        $response = $this->get('/attendance/list');

        $thisYearMonth = carbon::now()->format('Y-m');
        $response->assertSee($thisYearMonth);

        //ランダムに詳細画面を選択
        $job = Job::with('breakTime')
            ->where('user_id', Auth::id()) //本番はAuth::id();
            ->where('date', 'LIKE', $thisYearMonth . '%')
            ->inRandomOrder() //randomに並べ替え
            ->first();

        $response = $this->get('/attendance/detail/' . $job->id);

        $response->assertSee("名前");
        $userName = User::where('id', $job->user_id)
            ->first()
            ->name;

        $response->assertSee($userName);
    }
    public function test_selected_date_displayed_at_my_detail()
    {
        //===========     test 10-2   ============/

        $response = $this->post('/login', [
            'email' => 'test1@test.com',   //勤怠登録済ユーザー
            'password' => 'abc12345',
        ]);
        $response = $this->get('/');
        $response = $this->get('/attendance/list');

        $thisYearMonth = carbon::now()->format('Y-m');
        $response->assertSee($thisYearMonth);

        //ランダムに詳細画面を選択
        $job = Job::with('breakTime')
            ->where('user_id', Auth::id()) //本番はAuth::id();
            ->where('date', 'LIKE', $thisYearMonth . '%')
            ->inRandomOrder() //randomに並べ替え
            ->first();

        $response = $this->get('/attendance/detail/' . $job->id);

        $response->assertSee("日付");
        $response->assertSee(Carbon::parse($job->date)->format('Y年'));
        $response->assertSee(Carbon::parse($job->date)->format('n月j日'));
    }
    public function test_clock_in_out_time_displayed_at_my_detail()
    {
        //===========     test 10-3   ============/

        $response = $this->post('/login', [
            'email' => 'test1@test.com',   //勤怠登録済ユーザー
            'password' => 'abc12345',
        ]);
        $response = $this->get('/');
        $response = $this->get('/attendance/list');

        $thisYearMonth = carbon::now()->format('Y-m');
        $response->assertSee($thisYearMonth);

        //ランダムに詳細画面を選択
        $job = Job::with('breakTime')
            ->where('user_id', Auth::id()) //本番はAuth::id();
            ->where('date', 'LIKE', $thisYearMonth . '%')
            ->inRandomOrder() //randomに並べ替え
            ->first();

        $response = $this->get('/attendance/detail/' . $job->id);

        $response->assertSee("出勤・退勤");
        $response->assertSee(Carbon::parse($job->job_start)->format('H:i'));
        $response->assertSee(Carbon::parse($job->job_finish)->format('H:i'));
    }
    public function test_break_start_and_return_time_displayed_at_my_detail()
    {
        //===========     test 10-4   ============/

        $response = $this->post('/login', [
            'email' => 'test1@test.com',   //勤怠登録済ユーザー
            'password' => 'abc12345',
        ]);
        $response = $this->get('/');
        $response = $this->get('/attendance/list');

        $thisYearMonth = carbon::now()->format('Y-m');
        $response->assertSee($thisYearMonth);

        //ランダムに詳細画面を選択
        $job = Job::with('breakTime')
            ->where('user_id', Auth::id()) //本番はAuth::id();
            ->where('date', 'LIKE', $thisYearMonth . '%')
            ->inRandomOrder() //randomに並べ替え
            ->first();

        $response = $this->get('/attendance/detail/' . $job->id);

        $response->assertSee("出勤・退勤");
        $breaks = BreakTime::where('job_id', $job->id)->get();
        foreach ($breaks as $break) {
            $response->assertSee(Carbon::parse($break->break_start)->format('H:i'));
            $response->assertSee(Carbon::parse($break->break_finish)->format('H:i'));
        }
    }
}
