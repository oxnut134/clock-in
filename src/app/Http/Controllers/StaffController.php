<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class StaffController extends Controller
{
    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        $user = User::where('email', $request->email)->first();

        // メール認証登録チェック
        if ($user && !$user->email_verified_at) {
            return back()->withErrors(['email' => 'メール認証登録が完了していません。認証メールを確認してください。']);
        }

        if (Auth::attempt($credentials)) {

            return redirect()->route('staff.home');
            //            return view('welcome');
        }

        return back()->withErrors(['email' => 'ログイン情報が登録されていません']);
    }

    public function register(RegisterRequest $request)
    {
        //dd($request);
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'password_confirmation' => Hash::make($request->password_confirmation),
        ]);

        // Fortifyの認証メールを送信
        if (method_exists($user, 'sendEmailVerificationNotification')) {
            $user->sendEmailVerificationNotification();
            //認証メールフォーム：/Notifications/CustomVerifyEmail.php
        }

        // 登録完了後のリダイレクト
        return redirect()->route('email.verify', [
            'email' => $request->email
        ]);
    }
    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken(); 

        return redirect()->route('login');
    }
}
