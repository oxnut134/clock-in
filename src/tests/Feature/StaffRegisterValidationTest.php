<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class StaffRegisterValidationTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    use RefreshDatabase; // データベースをリフレッシュするトレイトを使用

    protected function setUp(): void
    {
        parent::setUp();
        // テーブルデータ作成
        User::create([
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => 'abc12345', // パスワードはハッシュ化
            'password_confirmation' => 'abc12345', // パスワードはハッシュ化
        ]);
    }
    public function test_register_name_empty_validation()
    {
        //***********     name  未入力  *****************/

        // ----------- POSTリクエスト送信 ----------

        $response = $this->post('/register', [
            'name' => '', //空データ
            'email' => 'test@test.com',
            'password' => 'abc12345',
            'password_confirmation' => 'abc12345',
        ]);

        // ステータスコード確認（302:リダイレクト）
        $response->assertStatus(302);

        // リダイレクト先からのレスポンスを取得
        $response = $this->get('/register');

        // エラーメッセージが画面に表示されているか確認
        $response->assertSee('お名前を入力してください');
    }
    public function test_register_email_empty_validation()
    {

        //***********     email 未入力   *****************/

        // ----------- POSTリクエスト送信 ----------------

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => '',   //空データ
            'password' => 'abc12345',
            'password_confirmation' => 'abc12345',
        ]);

        // ステータスコード確認（302:リダイレクト）
        $response->assertStatus(302);

        // リダイレクト先からのレスポンスを取得
        $response = $this->get('/register');

        // エラーメッセージが画面に表示されているか確認
        $response->assertSee('メールアドレスを入力してください');
    }
    public function test_register_password_mismatch_validation()
    {

        //*****     password 不一致   *****************/

        // ----------- POSTリクエスト送信 ----------------

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => 'abc12345',  //不一致
            'password_confirmation' => 'aac12345',
        ]);

        // ステータスコード確認（302:リダイレクト）
        $response->assertStatus(302);

        // リダイレクト先からのレスポンスを取得
        $response = $this->get('/register');

        // エラーメッセージが画面に表示されているか確認
        $response->assertSee('パスワードと一致しません');
    }
    public function test_register_less_than_8_validation()
    {

        //*******     password ８文字以下   *****************/

        // ----------- POSTリクエスト送信 ----------------

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => 'abc1234',  //７文字
            'password_confirmation' => 'abc12345',
        ]);

        // ステータスコード確認（302:リダイレクト）
        $response->assertStatus(302);

        // リダイレクト先からのレスポンスを取得
        $response = $this->get('/register');

        // エラーメッセージが画面に表示されているか確認
        $response->assertSee('パスワードは８文字以上で入力してください');
    }
    public function test_register_password_empty_validation()
    {

        //************     password 未入力  *****************/

        // ----------- POSTリクエスト送信 ----------------

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => '',  //空データ
            'password_confirmation' => 'abc12345',
        ]);

        // ステータスコード確認（302:リダイレクト）
        $response->assertStatus(302);

        // リダイレクト先からのレスポンスを取得
        $response = $this->get('/register');

        // エラーメッセージが画面に表示されているか確認
        $response->assertSee('パスワードを入力してください');
    }
}
