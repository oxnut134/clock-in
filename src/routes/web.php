<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
*/

// ****************** スタッフ認証　-**************************


use App\Http\Controllers\StaffController;
// ログイン
Route::get('/login', function () {
    return view('auth.login');
})->name('login');
Route::post('/login', [StaffController::class, 'login']);

// 会員登録
Route::get('/register', function () {
    return view('auth.register');
})->name('register');
Route::post('/register', [StaffController::class, 'register']);

// メール認証誘導
Route::get('/mail/verify/{email}', function ($email) {
    return view('emails.guide_verification', ['email' => $email]);
})->name('email.verify');


//------------- ミドルウェア　Auth　によるスタッフ登録認証 -------------------

Route::middleware('auth')->group(function () {

    Route::get('/', function () {
        return view('welcome');
    })->name('staff.home');

});

//--------------------  メール認証機能　カスタマイズ　----------------------------

use Laravel\Fortify\Features;
use Illuminate\Http\Request;
use App\Models\User;

// Fortifyのメール認証機能が有効の場合のみ処理
if (Features::enabled(Features::emailVerification())) {
    // 認証ページの表示
    Route::get('/email/verify', function () {
        return view('auth.verify-email'); // 認証通知ページを表示
    })->middleware('auth')->name('verification.notice'); // 認証済みユーザーのみアクセス可能

    // email_verified_at フィールド更新
    Route::get('/email/verify/{id}/{hash}', function (Request $request, $id, $hash) {
        $user = \App\Models\User::findOrFail($id); // IDでユーザーを取得

        // リンクが有効な場合
        if ($request->hasValidSignature()) {
            if (!$user->hasVerifiedEmail()) { // メールが未認証の場合のみ処理を実行
                $user->markEmailAsVerified(); // email_verified_at フィールドを現在の日時で更新
            }

            return redirect()->route('staff.home')->with('success', 'メールアドレスが認証されました。'); // 成功メッセージを表示しトップページへリダイレクト
        }

        return redirect()->route('staff.home')->with('error', '無効なリンクです。'); // エラーメッセージを表示しホームへリダイレクト
    })->middleware(['signed'])->name('verification.verify'); // 署名が有効なリンク

    // ------------------- 認証メールの再送信 ---------------------------

    //ログイン済ユーザー専用
    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification(); // 認証メールを再送信

        return back()->with('success', '認証メールを再送信しました。'); // 成功メッセージを表示し元のページに戻る
    })->middleware(['auth', 'throttle:6,1'])->name('verification.send.login'); // 認証済みユーザーのみアクセス可能で、リクエスト数を制限

    //ログイン済ユーザー、未ログインユーザー兼用
    Route::post('/email/verification-notification', function (Request $request) {
        // 入力されたメールアドレスを取得
        $email = $request->input('email');
        // メールアドレスに一致するユーザーを検索
        $user = User::where('email', $email)->first();
        // ユーザーが存在しない場合
        if (!$user) {
            return back()->with('error', '該当するユーザーが見つかりません。');
        }
        // ユーザーがすでにメール認証済みの場合
        if ($user->hasVerifiedEmail()) {
            return back()->with('info', 'このメールアドレスはすでに認証されています。');
        }
        // 認証メールを再送信
        $user->sendEmailVerificationNotification();

        return back()->with('success', '認証メールを再送信しました。');
    })->name('verification.send');
}

//************************** 管理者認証 *******************************

use App\Http\Controllers\AdminController;

// 管理者ログイン
Route::get('/admin/login', function () {
    return view('auth.login_admin');
})->name('admin.login');
Route::post('/admin/login', [AdminController::class, 'login']);

Route::get('/admin/home', function () {
    return view('welcome');
})->name('admin.home');

//------------- ミドルウェア　guest:admin　による管理者登録認証 -------------------
//Auth:admin適用
Route::middleware('guest:admin')->group(function () {

    Route::get('/admin', function () {
        return view('welcome');
    });//->name('admin.home');

});

//---------------------　--------------------------------------
Route::post('/admin/logout', [AdminController::class, 'logout'])->name('admin.logout');

//----------------- for making views ------------------
Route::get('/clock_in', function (){
    return view('/staff/clock_in');
});
Route::get('/clock_break', function (){
    return view('/staff/clock_break');
});
Route::get('/clock_return', function (){
    return view('/staff/clock_return');
});
Route::get('/clock_out', function (){
    return view('/staff/clock_out');
});
Route::get('/my_attendance', function (){
    return view('/staff/my_attendance');
});
Route::get('/my_detail', function (){
    return view('/staff/my_detail');
});
Route::get('/my_detail_applied', function (){
    return view('/staff/my_detail_applied');
});
Route::get('/my_applies', function (){
    return view('/staff/my_applies');
});
Route::get('/todays_staffs', function (){
    return view('/admin/todays_staffs');
});
Route::get('/todays_staff_detail', function (){
    return view('/admin/todays_staff_detail');
});
Route::get('/staffs', function (){
    return view('/admin/staffs');
});
Route::get('/staff_attendance', function (){
    return view('/admin/staff_attendance');
});
Route::get('/staff_applies', function (){
    return view('/admin/staff_applies');
});
Route::get('/approve', function (){
    return view('/admin/approve');
});
Route::get('/approved', function (){
    return view('/admin/approved');
});
/*
Route::get('/', function () {
    return view('welcome');
});
Route::get('/login', function () {'
    return view('auth.login_staff');
});
Route::get('/register', function () {
    return view('auth.register_staff');
});
Route::get('/login_admin', function () {
    return view('auth.login_admin');
});
*/
