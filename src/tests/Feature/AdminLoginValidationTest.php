<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\admin;

class AdminLoginValidationTest extends TestCase
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
        Admin::create([
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('abc12345'),
        ]);
    }
    public function test_login_email_empty_validation()
    {

        //===========     email 未入力   ============/

        // ----------- POSTリクエスト送信 ----------------

        $response = $this->post('/admin/login', [
            'email' => '',   //空データ
            'password' => 'abc12345',
        ]);

        // ステータスコード確認（302:リダイレクト）
        $response->assertStatus(302);

        // リダイレクト先からのレスポンスを取得
        $response = $this->get('/login');

        // エラーメッセージが画面に表示されているか確認
        $response->assertSee('メールアドレスを入力してください');
    }
    public function test_login_password_empty_validation()
    {

        //============     password 未入力  =================/

        // ----------- POSTリクエスト送信 ----------------

        $response = $this->post('/admin/login', [
            'email' => 'admin@test.com',
            'password' => '',  //空データ
        ]);

        // ステータスコード確認（302:リダイレクト）
        $response->assertStatus(302);

        // リダイレクト先からのレスポンスを取得
        $response = $this->get('/login');

        // エラーメッセージが画面に表示されているか確認
        $response->assertSee('パスワードを入力してください');
    }
    public function test_login_email_mismatch_validation()
    {

        //===========     email 未登録   =================/

        // ----------- POSTリクエスト送信 ----------------

        $response = $this->post('/login', [
            'email' => 'aamin@test.com',   //email未登録
            'password' => 'abc12345',
        ]);

        // ステータスコード確認（302:リダイレクト）
        $response->assertStatus(302);


        // リダイレクト先からのレスポンスを取得
        $response = $this->get('/login');

        // エラーメッセージが画面に表示されているか確認
        $response->assertSee('ログイン情報が登録されていません');
    }
    public function test_login_password_mismatch_validation()
    {

        //=====     password 不一致   =================/

        // ----------- POSTリクエスト送信 ----------------

        $response = $this->post('/login', [
            'email' => 'admin@test.com',
            'password' => 'aac12345',  //不一致
        ]);

        // ステータスコード確認（302:リダイレクト）
        $response->assertStatus(302);

        // リダイレクト先からのレスポンスを取得
        $response = $this->get('/login');

        // エラーメッセージが画面に表示されているか確認
        $response->assertSee('ログイン情報が登録されていません');
    }
}
