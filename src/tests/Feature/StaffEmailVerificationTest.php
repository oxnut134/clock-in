<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use DOMDocument;
use Illuminate\Auth\Notifications\VerifyEmail;


class StaffEmailVerificationTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }
    public function testAccessMailHogSite()
    {
        // ----------- 16-1 --------------

        $response = $this->post('/register', [
            'name' => 'Test',
            'email' => 'test@test.com',
            'password' => 'abc12345',
            'password_confirmation' => 'abc12345',
        ]);

        // ステータスコード確認（302:リダイレクト）
        $response->assertStatus(302);
        $user = User::where('email', 'test@test.com')->first();
        $this->actingAs($user); //ログイン状態にする
        $response = $this->get('/mail/verify/' . $user->email);
        $mailHogUrl = 'http://host.docker.internal:8025/api/v1/messages';
        $mailResponse = Http::get($mailHogUrl);

        $response->assertSee($user->email); //アドレス確認

    }
    public function testTransitionToVerificationPageFromVerificationGuide()
    {
        // ----------- 16-2 --------------

        $response = $this->post('/register', [
            'name' => 'Test',
            'email' => 'test@test.com',
            'password' => 'abc12345',
            'password_confirmation' => 'abc12345',
        ]);
        $response->assertStatus(302);

        $user = User::where('email', 'test@test.com')->first();
        $this->actingAs($user);

        $mailHogUrl = 'http://host.docker.internal:8025';
        $mailResponse = Http::get($mailHogUrl);

        $response->assertSee('test@test.com');
    }
    public function testTransitionToClockInByFinishMailVerification()
    {
        // ----------- 16-3 --------------

        $response = $this->post('/register', [
            'name' => 'Test',
            'email' => 'test@test.com',
            'password' => 'abc12345',
            'password_confirmation' => 'abc12345',
        ]);
        $response->assertStatus(302);

        $user = User::where('email', 'test@test.com')->first();
        $this->actingAs($user);

        $notifiable = $user;
        $notification = new VerifyEmail(); // VerifyEmailのインスタンス生成

        // toMailメソッドによるメールメッセージ取得
        $mailMessage = $notification->toMail($notifiable);

        // メールメッセージからURL取得
        $verificationUrl = $mailMessage->actionUrl; // actionUrlプロパティを使用

        // URLの生成を確認
        $this->assertNotNull($verificationUrl);

        // 認証URLにアクセス
        $response = $this->get($verificationUrl);
        $response = $this->get('/');

        $response->assertSee('勤務外');

    }
 }
