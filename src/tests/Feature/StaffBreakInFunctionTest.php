<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Job;
use App\Models\BreakTime;
use Carbon\Carbon;

class StaffBreakInFunctionTest extends TestCase
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
        //--- Job ----
        $users = User::all();
        $userFirst = User::query()->orderBy('id', 'asc')->first();
        flush();
        $jobs = [];
        $i = 1;
        foreach ($users as $user) {
            if ($user['id'] > $userFirst['id'] && $i <= $users->count()) {
                $date = Carbon::now()->format('Y-m-d');
                $job = Job::create([
                    'user_id' => $user['id'],
                    'date' => $date,
                ]);
                $jobs[] = $job; // 配列に保存
            }
            $i++;
        }
        foreach ($jobs as $index => $job) {
            switch ($index) {
                case 0:
                    $job->job_start = '08:55:00';
                    $job->job_finish = null;
                    $job->job_status = 'normal';
                    break;
                case 1:
                    $job->job_start = '08:50:00';
                    $job->job_finish = null;
                    $job->job_status = 'normal';
                    break;
                case 2:
                    $job->job_start = '08:55:00';
                    $job->job_finish = null;
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
                    $break->break_finish = null;
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
    public function test_function_break_in_button()
    {
        //===========     test 7-1   ============/

        $response = $this->post('/login', [
            'email' => 'User2@test.com',   //出勤中ユーザー
            'password' => 'abc12345',
        ]);
        $response = $this->get('/');
        $response->assertSee('休憩入');
        $user = User::where('email', "User2@test.com")->first();
        $job = Job::where('user_id', $user->id)->first();
        $job_id = $job->id;
        $response = $this->post('/clock/break', [
            'job_id' => $job_id,
        ]);
        $response->assertStatus(200);
        $response->assertSee('休憩中');
        $response = $this->post('/staff/logout');
    }
    public function test_function_multi_breaks()
    {
        //===========     test 7-2   ============/

        $response = $this->post('/login', [
            'email' => 'User2@test.com',   //出勤中ユーザー
            'password' => 'abc12345',
        ]);
        $response = $this->get('/');
        $response->assertSee('休憩入');
        $user = User::where('email', "User2@test.com")->first();
        $job = Job::where('user_id', $user->id)->first();
        $job_id = $job->id;
        $response = $this->post('/clock/break', [
            'job_id' => $job_id,
        ]);
        $response->assertStatus(200);
        $response->assertSee('休憩中');

        $break = BreakTime::where('job_id', $job_id)->first();
        $response = $this->post('/clock/return', [
            'break_id' => $break->id,
        ]);
        $response->assertStatus(200);
        $response->assertSee('休憩入');

        $response = $this->post('/staff/logout');
    }
    public function test_function_break_return_button()
    {
        //===========     test 7-3   ============/

        $response = $this->post('/login', [
            'email' => 'User2@test.com',   //出勤中ユーザー
            'password' => 'abc12345',
        ]);
        $response = $this->get('/');
        $response->assertSee('休憩入');
        //休憩入処理
        $user = User::where('email', "User2@test.com")->first();
        $job = Job::where('user_id', $user->id)->first();
        $job_id = $job->id;
        $response = $this->post('/clock/break', [
            'job_id' => $job_id,
        ]);
        $response->assertStatus(200);
        $response->assertSee('休憩戻');

        //休憩戻処理
        $break = BreakTime::where('job_id', $job_id)->first();
        $response = $this->post('/clock/return', [
            'break_id' => $break->id,
        ]);
        $response->assertStatus(200);
        $response->assertSee('出勤中');

        $response = $this->post('/staff/logout');
    }
    public function test_function_multi_break_return()
    {
        //===========     test 7-4   ============/

        $response = $this->post('/login', [
            'email' => 'User2@test.com',   //出勤中ユーザー
            'password' => 'abc12345',
        ]);
        $response = $this->get('/');
        $response->assertSee('休憩入');
        //休憩入処理
        $user = User::where('email', "User2@test.com")->first();
        $job = Job::where('user_id', $user->id)->first();
        $job_id = $job->id;
        $response = $this->post('/clock/break', [
            'job_id' => $job_id,
        ]);
        $response->assertStatus(200);
        $response->assertSee('休憩戻');

        //休憩戻処理
        $break = BreakTime::where('job_id', $job_id)->first();
        $response = $this->post('/clock/return', [
            'break_id' => $break->id,
        ]);
        $response->assertStatus(200);
        $response->assertSee('出勤中');

        //休憩再入処理
        $response = $this->post('/clock/break', [
            'job_id' => $job_id,
        ]);
        $response->assertStatus(200);
        $response->assertSee('休憩戻');

        $response = $this->post('/staff/logout');
    }
    public function test_display_time_of_break_duration()
    {
        //===========     test 7-5   ============/

        $response = $this->post('/login', [
            'email' => 'User2@test.com',   //出勤中ユーザー
            'password' => 'abc12345',
        ]);
        $response = $this->get('/');
        $response->assertSee('休憩入');
        //休憩入処理
        $user = User::where('email', "User2@test.com")->first();
        $job = Job::where('user_id', $user->id)->first();
        $job_id = $job->id;
        $response = $this->post('/clock/break', [
            'job_id' => $job_id,
        ]);
        $response->assertStatus(200);
        $response->assertSee('休憩戻');

        //休憩戻処理
        $break = BreakTime::where('job_id', $job_id)->first();
        $response = $this->post('/clock/return', [
            'break_id' => $break->id,
        ]);
        $response->assertStatus(200);
        $response->assertSee('出勤中');

        //勤怠一覧表示
        $response = $this->get('/attendance/list');

        $breakDuration = $break->calculateDuration();
         $breakDuration = Carbon::parse( $breakDuration)->format('H:i');
        $response->assertSee($breakDuration);


    }
}
