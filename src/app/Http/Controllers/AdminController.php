<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::guard('admin')->attempt($credentials)) {
            //return view('wellcome');
            return redirect()->route('admin.home'); // 管理者専用のホーム画面にリダイレクト
        }
//dd('ログイン失敗: ', $credentials);
        return back()->withErrors(['email' => 'ログイン情報が登録されていません']);
    }
    public function logout(Request $request)
{
    Auth::guard('admin')->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('admin.login');
}

}

