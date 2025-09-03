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


class StaffMyDetailAppliedToUpdateTest extends TestCase
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

    public function testJobStartAfterJobFinishValidation()
    {
        //===========     test 11-1   ============/

        $response = $this->post('/login', [
            'email' => 'test1@test.com',   //勤怠登録済ユーザー
            'password' => 'abc12345',
        ]);
        $response = $this->get('/');
        $response = $this->get('/attendance/list');

        $thisYearMonth = carbon::now()->format('Y-m');

        //ランダムに詳細画面を選択
        $job = Job::with('breakTime')
            ->where('user_id', Auth::id()) //本番はAuth::id();
            ->where('date', 'LIKE', $thisYearMonth . '%')
            ->where('job_status', "normal")
            ->inRandomOrder() //randomに並べ替え
            ->first();
        $breaks = BreakTime::where('job_id', $job->id)->get();

        $response = $this->get('/attendance/detail/' . $job->id);

        // データを配列に変換
        foreach ($breaks as $break) {
            $array = [
                'job_start' => "19:00", // 開始時間
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
        $response = $this->post('/apply', $array);
        $response = $this->get('/attendance/detail/' . $job->id);

        $response->assertSee("出勤時間が不適切な時刻です");

        $response = $this->post('/staff/logout');
    }


    public function testBreakStartAfterJobFinishValidation()
    {
        //===========     test 11-2   ============/

        $response = $this->post('/login', [
            'email' => 'test1@test.com',   //勤怠登録済ユーザー
            'password' => 'abc12345',
        ]);
        $response = $this->get('/');
        $response = $this->get('/attendance/list');

        $thisYearMonth = carbon::now()->format('Y-m');

        //ランダムに詳細画面を選択
        $job = Job::with('breakTime')
            ->where('user_id', Auth::id()) //本番はAuth::id();
            ->where('date', 'LIKE', $thisYearMonth . '%')
            ->where('job_status', "normal")
            ->inRandomOrder() //randomに並べ替え
            ->first();
        $breaks = BreakTime::where('job_id', $job->id)->get();

        $response = $this->get('/attendance/detail/' . $job->id);

        // データを配列に変換
        foreach ($breaks as $break) {
            $array = [
                'job_start' => Carbon::parse($job->job_start)->format('H:i'),
                'job_finish' => Carbon::parse($job->job_finish)->format('H:i'),
                'breakTimes' => [], // ブレイクタイムの初期化
                'remark' => "test", // 備考
                'id' => $job->id, // ジョブID
            ];
            $array['breakTimes'][] = [
                'id' => $break->id,
                'break_start' => "19:00",
                'break_finish' => Carbon::parse($break->break_finish)->format('H:i'),
            ];
        }
        $response = $this->post('/apply', $array);
        $response = $this->get('/attendance/detail/' . $job->id);

        $response->assertSee("休憩時間が不適切な時刻です");

        $response = $this->post('/staff/logout');
    }
    public function testBreakFinishAfterJobFinishValidation()
    {
        //===========     test 11-3   ============/

        $response = $this->post('/login', [
            'email' => 'test1@test.com',   //勤怠登録済ユーザー
            'password' => 'abc12345',
        ]);
        $response = $this->get('/');
        $response = $this->get('/attendance/list');

        $thisYearMonth = carbon::now()->format('Y-m');

        //ランダムに詳細画面を選択
        $job = Job::with('breakTime')
            ->where('user_id', Auth::id()) //本番はAuth::id();
            ->where('date', 'LIKE', $thisYearMonth . '%')
            ->where('job_status', "normal")
            ->inRandomOrder() //randomに並べ替え
            ->first();
        $breaks = BreakTime::where('job_id', $job->id)->get();

        $response = $this->get('/attendance/detail/' . $job->id);

        // データを配列に変換
        foreach ($breaks as $break) {
            $array = [
                'job_start' => Carbon::parse($job->job_start)->format('H:i'),
                'job_finish' => Carbon::parse($job->job_finish)->format('H:i'),
                'breakTimes' => [], // ブレイクタイムの初期化
                'remark' => "test", // 備考
                'id' => $job->id, // ジョブID
            ];
            $array['breakTimes'][] = [
                'id' => $break->id,
                'break_start' => Carbon::parse($break->break_start)->format('H:i'),
                'break_finish' => "19:00"
            ];
        }
        $response = $this->post('/apply', $array);
        $response = $this->get('/attendance/detail/' . $job->id);

        $response->assertSee("休憩時間もしくは退勤時間が不適切な時刻です");

        $response = $this->post('/staff/logout');
    }
    public function testNotInputRemarkValidation()
    {
        //===========     test 11-4   ============/

        $response = $this->post('/login', [
            'email' => 'test1@test.com',   //勤怠登録済ユーザー
            'password' => 'abc12345',
        ]);
        $response = $this->get('/');
        $response = $this->get('/attendance/list');

        $thisYearMonth = carbon::now()->format('Y-m');

        //ランダムに詳細画面を選択
        $job = Job::with('breakTime')
            ->where('user_id', Auth::id()) //本番はAuth::id();
            ->where('date', 'LIKE', $thisYearMonth . '%')
            ->where('job_status', "normal")
            ->inRandomOrder() //randomに並べ替え
            ->first();
        $breaks = BreakTime::where('job_id', $job->id)->get();

        $response = $this->get('/attendance/detail/' . $job->id);

        // データを配列に変換
        foreach ($breaks as $break) {
            $array = [
                'job_start' => Carbon::parse($job->job_start)->format('H:i'),
                'job_finish' => Carbon::parse($job->job_finish)->format('H:i'),
                'breakTimes' => [], // ブレイクタイムの初期化
                'remark' => null, // <<================  備考 :null
                'id' => $job->id, // ジョブID
            ];
            $array['breakTimes'][] = [
                'id' => $break->id,
                'break_start' => Carbon::parse($break->break_start)->format('H:i'),
                'break_finish' => Carbon::parse($break->break_finish)->format('H:i'),
            ];
        }
        $response = $this->post('/apply', $array);
        $response = $this->get('/attendance/detail/' . $job->id);

        $response->assertSee("備考を記入してください");

        $response = $this->post('/staff/logout');
    }

    public function testCompleteAppliesAndShownInAdminList()
    {
        //===========     test 11-5   ============/

        $response = $this->post('/login', [
            'email' => 'test1@test.com',   //勤怠登録済ユーザー
            'password' => 'abc12345',
        ]);
        $response = $this->get('/');
        $response = $this->get('/attendance/list');

        $thisYearMonth = carbon::now()->format('Y-m');
        //ランダムに詳細画面を選択
        $job = Job::with('breakTime')
            ->where('user_id', Auth::id()) //本番はAuth::id();
            ->where('date', 'LIKE', $thisYearMonth . '%')
            ->where('job_status', "normal")
            ->inRandomOrder() //randomに並べ替え
            ->first();
        $breaks = BreakTime::where('job_id', $job->id)->get();

        $response = $this->get('/attendance/detail/' . $job->id);
        // データを配列に変換
        foreach ($breaks as $break) {
            $array = [
                'job_start' => "10:00",
                'job_finish' => Carbon::parse($job->job_finish)->format('H:i'),
                'breakTimes' => [], // ブレイクタイムの初期化
                'remark' => "test", //
                'id' => $job->id, // ジョブID
            ];
            $array['breakTimes'][] = [
                'id' => $break->id,
                'break_start' => Carbon::parse($break->break_start)->format('H:i'),
                'break_finish' => Carbon::parse($break->break_finish)->format('H:i'),
            ];
        }
        //申請処理
        $response = $this->post('/apply', $array);
        $response = $this->get('/attendance/detail/' . $job->id);
        $job = Job::find($job->id);
        $this->assertEquals('applied', $job->job_status, 'Job status is not "applied"');

        //adminログイン
        $response = $this->post('/admin/login', [
            'email' => 'Admin1@test.com',   //管理者メールアドレス
            'password' => 'abc12345',
        ]);
        $response->assertStatus(302);

        //再度jobインスタンス取得
        $appliedJob = Job::with(['breakTime', 'user'])
            ->where('user_id', $job->user_id)
            ->where('date', 'LIKE', $thisYearMonth . '%')
            ->where('job_status', "applied")
            ->first();
        //breakインスタンス取得
        $breakTime = BreakTime::where('job_id', $appliedJob->id)->first();

        $response = $this->get('/admin/attendance');
        $response = $this->get('/admin/requests?param=applied');

        //承認一覧画面確認
        $response->assertSee("承認待ち");
        $response->assertSee($appliedJob->user->name);
        $response->assertSee(Carbon::parse($appliedJob->date)->format('Y/m/d'));
        $response->assertSee($appliedJob->remark);
        $response->assertSee(Carbon::parse($appliedJob->updated_at)->format('Y/m/d'));
        $this->assertEquals('applied', $job->job_status);

        //修正申請表示確認
        $response = $this->get('/admin/requests/' . $appliedJob->id);

        $response->assertSee($appliedJob->user->name);
        $response->assertSee(Carbon::parse($appliedJob->date)->format('Y年'));
        $response->assertSee(Carbon::parse($appliedJob->date)->format('n月j日'));
        $response->assertSee(Carbon::parse($appliedJob->job_start)->format('H:i'));
        $response->assertSee(Carbon::parse($appliedJob->job_finish)->format('H:i'));
        $response->assertSee(Carbon::parse($breakTime->break_start)->format('H:i'));
        $response->assertSee(Carbon::parse($breakTime->break_finish)->format('H:i'));
        $response->assertSee($appliedJob->remark);
    }
    public function testAllMyAppliesShownInAdminList()
    {
        //===========     test 11-6   ============/

        $response = $this->post('/login', [
            'email' => 'test1@test.com',   //勤怠登録済ユーザー
            'password' => 'abc12345',
        ]);
        $response = $this->get('/');
        $response = $this->get('/attendance/list');

        $thisYearMonth = carbon::now()->format('Y-m');

        //複数回修正申請
        $maxCount = 10;
        $step = 2;
        $counter = 0;
        while ($counter < $maxCount) {
            //詳細画面を選択
            $exit = false;
            while (!$exit) {
                $job = Job::with('breakTime')
                    ->where('user_id', Auth::id()) //本番はAuth::id();
                    ->where('date', 'LIKE', $thisYearMonth . '%')
                    ->where('job_status', "normal")
                    ->inRandomOrder() //randomに並べ替え
                    //->skip($counter) //レコードをスキップ
                    ->first();

                if ($job->job_status != 'applied') {
                    $exit = true;
                }
            }
            $breaks = BreakTime::where('job_id', $job->id)->get();
            // データを配列に変換
            foreach ($breaks as $break) {
                $array = [
                    'job_start' => "10:00",
                    'job_finish' => Carbon::parse($job->job_finish)->format('H:i'),
                    'breakTimes' => [], // ブレイクタイムの初期化
                    'remark' => "test", //
                    'id' => $job->id, // ジョブID
                ];
                $array['breakTimes'][] = [
                    'id' => $break->id,
                    'break_start' => Carbon::parse($break->break_start)->format('H:i'),
                    'break_finish' => Carbon::parse($break->break_finish)->format('H:i'),
                ];
            }

            //申請処理
            $response = $this->post('/apply', $array);
            $response = $this->get('/attendance/detail/' . $job->id);
            $job = Job::find($job->id);
            $this->assertEquals('applied', $job->job_status, 'Job status is not "applied"');

            $counter += $step;
        }

        //再度jobインスタンス取得
        $appliedJobs = Job::with(['breakTime', 'user'])
            ->where('user_id', $job->user_id)
            ->where('date', 'LIKE', $thisYearMonth . '%')
            ->where('job_status', "applied")
            //->skip($counter) //レコードをスキップ
            ->get();
        foreach ($appliedJobs as $appliedJob) {
            //breakインスタンス取得
            $breakTime = BreakTime::where('job_id', $appliedJob->id)->first();
            $dummyId = $breakTime->id;
            $response = $this->get('/stamp_correction_request/list/applied');

            $response->assertSee("申請一覧");
            $response->assertSee("承認待ち");
            $response->assertSee($appliedJob->user->name);
            $response->assertSee(Carbon::parse($appliedJob->date)->format('Y/m/d'));
            $response->assertSee($appliedJob->remark);
            $response->assertSee(Carbon::parse($appliedJob->updated_at)->format('Y/m/d'));
            $this->assertEquals('applied', $job->job_status);
        }
        $response = $this->post('/staff/logout');
    }
    public function testAllApprovedAppliesDisplayed()
    {
        //===========     test 11-7   ============/
        $response = $this->post('/login', [
            'email' => 'test1@test.com',   //勤怠登録済ユーザー
            'password' => 'abc12345',
        ]);
        $response = $this->get('/');
        $response = $this->get('/attendance/list');

        $thisYearMonth = carbon::now()->format('Y-m');

        $maxCount = 10;
        $step = 2;
        $counter = 0;
        while ($counter < $maxCount) {
            //詳細画面を選択
            $exit = false;
            while (!$exit) {
                //詳細画面を選択
                $job = Job::with('breakTime')
                    ->where('user_id', Auth::id()) //本番はAuth::id();
                    ->where('date', 'LIKE', $thisYearMonth . '%')
                    ->where('job_status', "normal")
                    ->inRandomOrder() //randomに並べ替え
                    ->first();
                if ($job->job_status != 'applied') {
                    $exit = true;
                }
            }
            $breaks = BreakTime::where('job_id', $job->id)->get();

            // データを配列に変換
            foreach ($breaks as $break) {
                $array = [
                    //                'job_start' => Carbon::parse($job->job_start)->format('H:i'),
                    'job_start' => "10:00",
                    'job_finish' => Carbon::parse($job->job_finish)->format('H:i'),
                    'breakTimes' => [], // ブレイクタイムの初期化
                    'remark' => "test", //
                    'id' => $job->id, // ジョブID
                ];
                $array['breakTimes'][] = [
                    'id' => $break->id,
                    'break_start' => Carbon::parse($break->break_start)->format('H:i'),
                    'break_finish' => Carbon::parse($break->break_finish)->format('H:i'),
                ];
            }
            $counter += $step;
        }
        //申請処理
        $response = $this->post('/apply', $array);
        $response = $this->get('/attendance/detail/' . $job->id);
        $job = Job::find($job->id);
        $this->assertEquals('applied', $job->job_status, 'Job status is not "applied"');


        //adminログイン
        $response = $this->post('/admin/login', [
            'email' => 'Admin1@test.com',   //管理者メールアドレス
            'password' => 'abc12345',
        ]);
        $response->assertStatus(302);

        //再度jobインスタンス取得
        $appliedJobs = Job::with(['breakTime', 'user'])
            ->where('user_id', $job->user_id)
            ->where('date', 'LIKE', $thisYearMonth . '%')
            ->where('job_status', "applied")
            //->skip($counter) //レコードをスキップ
            ->get();

        foreach ($appliedJobs as $appliedJob) {
            //申請一覧(承認待ち)表示確認
            //breakインスタンス取得
            $breakTime = BreakTime::where('job_id', $appliedJob->id)->first();
            $response = $this->get('/stamp_correction_request/list/applied');

            $response->assertSee("申請一覧");
            $response->assertSee("承認待ち");
            $response->assertSee($appliedJob->user->name);
            $response->assertSee(Carbon::parse($appliedJob->date)->format('Y/m/d'));
            $response->assertSee($appliedJob->remark);
            $response->assertSee(Carbon::parse($appliedJob->updated_at)->format('Y/m/d'));
            $this->assertEquals('applied', $appliedJob->job_status);

            //管理者承認
            $response = $this->get('/admin/requests/' . $appliedJob->id);
            $response = $this->post('/approve', [
                'job_id' => $appliedJob->id,
            ]);

            //承認済み表示
            $response = $this->get('/admin/requests?param=approved');

            $response->assertSee("申請一覧");
            $response->assertSee("承認済み");
            $response->assertSee($appliedJob->user->name);
            $response->assertSee(Carbon::parse($appliedJob->date)->format('Y/m/d'));
            $response->assertSee($appliedJob->remark);
            $response->assertSee(Carbon::parse($appliedJob->updated_at)->format('Y/m/d'));

            $approvedJobs = Job::with(['breakTime', 'user'])
                ->where('user_id', $job->user_id)
                ->where('date', 'LIKE', $thisYearMonth . '%')
                ->where('job_status', "approved")
                //->skip($counter) //レコードをスキップ
                ->get();
            foreach ($approvedJobs as $approvedJob)
                $this->assertEquals('approved', $approvedJob->job_status);
        }
    }
    public function testDetailButtonFunctionOnEachPages()
    {
        //===========     test 11-8   ============/

        $response = $this->post('/login', [
            'email' => 'test1@test.com',   //勤怠登録済ユーザー
            'password' => 'abc12345',
        ]);
        $response = $this->get('/');
        $response = $this->get('/attendance/list');

        $thisYearMonth = carbon::now()->format('Y-m');
        //ランダムに詳細画面を選択
        $job = Job::with('breakTime')
            ->where('user_id', Auth::id()) //本番はAuth::id();
            ->where('date', 'LIKE', $thisYearMonth . '%')
            ->where('job_status', "normal")
            ->inRandomOrder() //randomに並べ替え
            ->first();
        $breaks = BreakTime::where('job_id', $job->id)->get();

        $response = $this->get('/attendance/detail/' . $job->id);
        // データを配列に変換
        foreach ($breaks as $break) {
            $array = [
                'job_start' => "10:00",
                'job_finish' => Carbon::parse($job->job_finish)->format('H:i'),
                'breakTimes' => [], // ブレイクタイムの初期化
                'remark' => "test", //
                'id' => $job->id, // ジョブID
            ];
            $array['breakTimes'][] = [
                'id' => $break->id,
                'break_start' => Carbon::parse($break->break_start)->format('H:i'),
                'break_finish' => Carbon::parse($break->break_finish)->format('H:i'),
            ];
        }
        //申請処理
        $response = $this->post('/apply', $array);
        $response = $this->get('/attendance/detail/' . $job->id);
        $job = Job::find($job->id);
        $this->assertEquals('applied', $job->job_status, 'Job status is not "applied"');

        //再度jobインスタンス取得
        $appliedJob = Job::with(['breakTime', 'user'])
            ->where('user_id', $job->user_id)
            ->where('date', 'LIKE', $thisYearMonth . '%')
            ->where('job_status', "applied")
            ->first();
        //breakインスタンス取得
        $breakTime = BreakTime::where('job_id', $appliedJob->id)->first();
        //申請一覧画面表示
        $response = $this->get('/attendance/list');
        //詳細ボタンクリック
        $response = $this->get('/attendance/detail/' . $appliedJob->id);

        //勤怠詳細画面表示
        $response->assertSee("勤怠詳細");
        $response->assertSee($appliedJob->user->name);
        $response->assertSee(Carbon::parse($appliedJob->date)->format('Y年'));
        $response->assertSee(Carbon::parse($appliedJob->date)->format('n月j日'));
        $response->assertSee(Carbon::parse($appliedJob->job_start)->format('H:i'));
        $response->assertSee(Carbon::parse($appliedJob->job_finish)->format('H:i'));
        $response->assertSee(Carbon::parse($breakTime->break_start)->format('H:i'));
        $response->assertSee(Carbon::parse($breakTime->break_finish)->format('H:i'));
        $response->assertSee($appliedJob->remark);
    }
}
