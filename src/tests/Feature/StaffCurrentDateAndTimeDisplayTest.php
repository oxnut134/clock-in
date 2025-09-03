<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Carbon\Carbon;

class StaffCurrentDateAndTimeDisplayTest extends TestCase
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
        User::create([
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => bcrypt('abc12345'),
            'password_confirmation' => bcrypt('abc12345'),
            'email_verified_at' => now(),

        ]);
    }
    public function test_current_date_time_display()
    {

        //===========     勤務外表示   ============/

        // ----------- login ----------------

        $response = $this->post('/login', [
            'email' => 'test@test.com',   //空データ
            'password' => 'abc12345',
        ]);

        // ステータスコード確認（302:リダイレクト）
        $response->assertStatus(302);

        // リダイレクト先からのレスポンスを取得
        $response = $this->get('/');

        //dd($request);
        $currentDateTime = Carbon::now();
        $date = $currentDateTime->format('Y年n月j日');
        $dayOfWeek = '(' . $currentDateTime->isoFormat('ddd') . ')';
        $time = $currentDateTime->format('H:i');

        // 画面表示確認
        $response->assertSee($date);
        $response->assertSee($dayOfWeek);
        $response->assertSee($time);
    }
}
