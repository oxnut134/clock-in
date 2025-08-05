<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        // 会員登録画面<?php

        Fortify::registerView(function () {
            //return view('auth.register');
            // null makes default /register route  disable
            return null;
        });

        // ログイン画面
        Fortify::loginView(function () {
            //return view('auth.login');
             // null makes default /login route  disable
            return null;
        });

        // メール認証誘導画面
        Fortify::verifyEmailView(function () {
            return view('auth.verify-email');
        });
    }
}

