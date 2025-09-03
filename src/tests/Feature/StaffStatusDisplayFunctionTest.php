<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Job;
use App\Models\BreakTime;

class StaffStatusDisplayFunctionTest extends TestCase
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
                $jobs[] = $job; // 作成したJobを配列に保存
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
                $breaks[] = $break; // 作成したJobを配列に保存
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
    public function testOffDutyDisplayed()
    {

        //===========     勤務外表示   ============/

        // ----------- 5-1 -------------

        $response = $this->post('/login', [
            'email' => 'User1@test.com',   //空データ
            'password' => 'abc12345',
        ]);

        // ステータスコード確認（302:リダイレクト）
        $response->assertStatus(302);

        // リダイレクト先からのレスポンスを取得
        $response = $this->get('/');

        // エラーメッセージが画面に表示されているか確認
        $response->assertSee('勤務外');

        //logout
        $response = $this->post('/staff/logout');
    }
    public function testAtDutyDisplayed()
    {

        //===========     出勤中表示   ============/

        // ----------- 5-2 -------------

        $response = $this->post('/login', [
            'email' => 'User2@test.com',   //空データ
            'password' => 'abc12345',
        ]);

        // ステータスコード確認（302:リダイレクト）
        $response->assertStatus(302);

        // リダイレクト先からのレスポンスを取得
        $response = $this->post('/');

        // エラーメッセージが画面に表示されているか確認
        $response->assertSee('出勤中');

        //logout
        $response = $this->post('/staff/logout');
    }
    public function testInBreakDisplayed()
    {

        //===========     休憩中表示   ============/

        // ----------- 5-3 -------------

        $response = $this->post('/login', [
            'email' => 'User3@test.com',   //空データ
            'password' => 'abc12345',
        ]);

        // ステータスコード確認（302:リダイレクト）
        $response->assertStatus(302);

        // リダイレクト先からのレスポンスを取得
        $job = Job::query()->orderBy('id')->skip(1)->first();
        $response = $this->post('/clock/break', [
            'job_id' => $job->id,
        ]);

        // エラーメッセージが画面に表示されているか確認
        $response->assertSee('休憩中');

        //logout
        $response = $this->post('/staff/logout');
    }
    public function testSecondAtDutyDisplayed()
    {

        //===========     出勤中表示   ============/

        // ----------- dummy -------------

        $response = $this->post('/login', [
            'email' => 'User4@test.com',   //空データ
            'password' => 'abc12345',
        ]);

        // ステータスコード確認（302:リダイレクト）
        $response->assertStatus(302);

        // リダイレクト先からのレスポンスを取得
        $job = Job::query()->orderBy('id')->skip(1)->first();
        $break =BreakTime::query()->first();
        $response = $this->post('/clock/return', [
            'job_id' => $job->id,
            'break_id' => $break->id,
        ]);

        // エラーメッセージが画面に表示されているか確認
        $response->assertSee('出勤中');

        //logout
        $response = $this->post('/staff/logout');
    }
    public function testAfterDutyDisplayed()
    {

        //===========     退勤済表示   ============/

        // ----------- 5-4 -------------

        $response = $this->post('/login', [
            'email' => 'User5@test.com',   //空データ
            'password' => 'abc12345',
        ]);

        // ステータスコード確認（302:リダイレクト）
        $response->assertStatus(302);

        // リダイレクト先からのレスポンスを取得
        $job = Job::query()->orderBy('id')->skip(2)->first();
        $break =BreakTime::query()->orderBy('id')->skip(1)->first();
        $response = $this->post('/clock/out', [
            'job_id' => $job->id,
            'break_id' => $break->id,
        ]);

        // エラーメッセージが画面に表示されているか確認
        $response->assertSee('退勤済');

        //logout
        $response = $this->post('/staff/logout');
    }
}
