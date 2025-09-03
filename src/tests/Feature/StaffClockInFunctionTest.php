<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Job;
use App\Models\BreakTime;
use Carbon\Carbon;

class StaffClockInFunctionTest extends TestCase
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
        //---- User -------
        $users = [];
        $user = User::create([
            'name' => 'User1',
            'email' => 'User1@test.com',
            'password' => bcrypt('abc12345'),
            'password_confirmation' => bcrypt('abc12345'),
            'email_verified_at' => now(),
        ]);

        $user = User::create([
            'name' => 'User2',
            'email' => 'User2@test.com',
            'password' => bcrypt('abc12345'),
            'password_confirmation' => bcrypt('abc12345'),
            'email_verified_at' => now(),

        ]);
        $user = User::create([
            'name' => 'User3',
            'email' => 'User3@test.com',
            'password' => bcrypt('abc12345'),
            'password_confirmation' => bcrypt('abc12345'),
            'email_verified_at' => now(),

        ]);
        $user = User::create([
            'name' => 'User4',
            'email' => 'User4@test.com',
            'password' => bcrypt('abc12345'),
            'password_confirmation' => bcrypt('abc12345'),
            'email_verified_at' => now(),

        ]);
        $user = User::create([
            'name' => 'User5',
            'email' => 'User5@test.com',
            'password' => bcrypt('abc12345'),
            'password_confirmation' => bcrypt('abc12345'),
            'email_verified_at' => now(),

        ]);
        //--- Job ----
        $users = User::all();
        $userFirst = User::query()->orderBy('id', 'asc')->first();
        flush();
        $jobs = [];
        $i = 1;
        foreach ($users as $user) {
            if ($user['id'] > $userFirst['id']  && $i <= $users->count()) {
                $job = Job::create([
                    'user_id' => $user['id'],
                    'date' => '2025-08-25',
                ]);
                $jobs[] = $job; // 配列に保存
            }
            $i++;
        }
        foreach ($jobs as $index => $job) {
            switch ($index) {
                case 0:
                    $job->job_start = '08:55:00';
                    $job->job_finish = '';
                    $job->job_status = 'normal';
                    break;
                case 1:
                    $job->job_start = '08:50:00';
                    $job->job_finish = '';
                    $job->job_status = 'normal';
                    break;
                case 2:
                    $job->job_start = '08:55:00';
                    $job->job_finish = '';
                    $job->job_status = 'normal';
                    break;
                case 3:
                    $job->job_start = '08:55:00';
                    $job->job_finish = '17:30:00';
                    $job->job_status = 'normal';
                    break;
            }
            $job->save(); // 変更を保存
        }

        $jobs = Job::all();
        $jobFirst = Job::query()->orderBy('id', 'asc')->first();
        $breaks = [];
        $i = 1;
        foreach ($jobs as $job) {
            if ($job['id'] > $jobFirst['id'] && $i <= $jobs->count()) {
                $break = BreakTime::create([
                    'job_id' => $job['id'],
                ]);
                $breaks[] = $break; // 配列に保存
            }
            $i++;
        }
        foreach ($breaks as $index => $break) {
            switch ($index) {
                case 0:
                    $break->break_start = '11:55:00';
                    $break->break_finish = '';
                    $break->break_status = 'normal';
                    break;
                case 1:
                    $break->break_start = '12:05:00';
                    $break->break_finish = '12:55:00';
                    $break->break_status = 'normal';
                    break;
                case 2:
                    $break->break_start = '12:00:00';
                    $break->break_finish = '13:05:00';
                    $break->break_status = 'normal';
                    break;
            }
            $break->save(); // 変更を保存
        }
    }
    public function test_function_clock_in_button()
    {
        //===========     test 6-1   ============/

        $response = $this->post('/login', [
            'email' => 'User1@test.com',   //勤務外ユーザー
            'password' => 'abc12345',
        ]);
        $response = $this->get('/');
        $response->assertSee('出勤');
        $response = $this->post('/');
        $response->assertSee('出勤中');
        $response = $this->post('/staff/logout');
    }
    public function test_only_once_clock_in()
    {
        //===========     test 6-2   ============/

        $response = $this->post('/login', [
            'email' => 'User5@test.com',   //退勤済ユーザー
            'password' => 'abc12345',
        ]);
        $response->assertDontSee('出勤');
        $response = $this->post('/staff/logout');
    }
    public function test_display_time_clock_in()
    {
        //===========     test 6-3   ============/

        $response = $this->post('/login', [
            'email' => 'User1@test.com',   //勤務外ユーザー
            'password' => 'abc12345',
        ]);
        $response = $this->get('/');
        $response = $this->post('/');
        $response = $this->get('/attendance/list');
        $currentTime = Carbon::now()->format('H:i');
        $oneMinuteAgo = Carbon::now()->subMinute()->format('H:i'); // 1分前の時刻

        $this->assertSeeEither($response, $currentTime, $oneMinuteAgo);

        $response = $this->post('/staff/logout');
    }
    //カスタムアサーション
    public function assertSeeEither($response, $text1, $text2)
    {
        $content = $response->getContent();
        $this->assertTrue(
            strpos($content, $text1) !== false ||
                strpos($content, $text2) !== false,
            "Neither '{$text1}' nor '{$text2}' was found in the response."
        );
    }
}
