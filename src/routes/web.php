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



//--------------------  メール認証機能　カスタマイズ　----------------------------

use Laravel\Fortify\Features;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
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
                $user->markEmailAsVerified(); // email_verified_atに保存
            }
            Auth::login($user); // ユーザーをログインさせる
            return redirect()->route('staff.home')->with('success', 'メールアドレスが認証されました。'); //トップページへリダイレクト
        }

        return redirect()->route('staff.home')->with('error', '無効なリンクです。'); // ホームへリダイレクト
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


// ****************** スタッフ認証　*****************************


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



//*************** スタッフ登録認証 ***********************
use App\Http\Controllers\MyAttendanceController;
use App\Http\Controllers\ScheduleAdjustController;


Route::middleware('auth')->group(function () {

    //-------------- staff logout ---------------------------

    Route::post('/staff/logout', [StaffController::class, 'logout'])
        ->name('staff.logout');

    // ***************** Staff Routines ************************************

    // ----------------- Clock in ------------------------


    Route::get('/', [MyAttendanceController::class, 'showClockIn'])->name('staff.home');
    Route::post('/', [MyAttendanceController::class, 'putClockIn']);
    Route::post('/clock/break', [MyAttendanceController::class, 'putClockBreak']);
    Route::post('/clock/return', [MyAttendanceController::class, 'putClockReturn']);
    Route::post('/clock/out', [MyAttendanceController::class, 'putClockOut']);

    //---------------- 勤怠一覧　-----------------------------

    Route::get('/attendance/list', [MyAttendanceController::class, 'getMyList'])->name('attendance.list');
    Route::post('attendance/last_month', [MyAttendanceController::class, 'showLastMonth'])->name('attendance.last_month');
    Route::post('attendance/next_month', [MyAttendanceController::class, 'showNextMonth'])->name('attendance.next_month');


    Route::get('/attendance/detail/{id}', [ScheduleAdjustController::class, 'getMyDetail'])->name('attendance.detail');
    Route::post('/apply', [ScheduleAdjustController::class, 'applyNewSchedule'])->name('apply');

    //-------------- 申請一覧　-----------------------

    Route::get('/stamp_correction_request/list/{param}', [ScheduleAdjustController::class, 'getMyApplyList'])->name('stamp_correction_request.list');
});



//*************************** 管理者認証 *******************************

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminAttendanceController;
use App\Http\Controllers\AdminScheduleAdjustController;


// 管理者ログイン
Route::get('/admin/login', function () {
    return view('auth.login_admin');
})->name('admin.login');
Route::post('/admin/login', [AdminController::class, 'login']);

//************** 管理者登録認証 （ ミドルウェア　guest:admin　使用） **************
//guest:admin適用
Route::middleware('guest:admin')->group(function () {


    Route::get('/admin', function () {
        return redirect()->route('admin.attendances');
    })->name('admin.home');

    //---------------------　admin logout--------------------------------------
    Route::post('/admin/logout', [AdminController::class, 'logout'])
        ->name('admin.logout');


    // ******************** Admin Routines *************************

    //--------------- 勤怠一覧（管理者）--------------------------

    Route::get('/admin/attendances', [AdminAttendanceController::class, 'getTodaysStaff'])->name('admin.attendances');
    Route::post('/admin/attendances/yesterday', [AdminAttendanceController::class, 'getYesterdaysStaff'])->name('admin.attendances/yesterday');
    Route::post('/admin/attendances/tomorrow', [AdminAttendanceController::class, 'getTomorrowsStaff'])->name('admin.attendances/tomorrow');

    //--------------- 勤怠詳細（管理者）----------------------------

    Route::get('admin/attendances/{id}', [AdminScheduleAdjustController::class, 'getTodaysStaffDetail'])->name('attendance.detail');
    Route::post('/admin/apply', [AdminScheduleAdjustController::class, 'applyNewSchedule'])->name('admin.apply');

    //---------------- スタッフ一覧（管理者）-----------------------------

    Route::get('/admin/users', [AdminAttendanceController::class, 'getStaffs'])->name('admin.users');

    //---------------- スタッフ別勤怠一覧 --------------------------------
    Route::get('/admin/users/{user}/attendances', [AdminAttendanceController::class, 'getMonthlyStaffJobs'])->name('admin.users.attendance');
    Route::post('/admin/users/attendances/last_month', [AdminAttendanceController::class, 'showLastMonth'])->name('attendance.last_month');
    Route::post('/admin/users/attendances/next_month', [AdminAttendanceController::class, 'showNextMonth'])->name('attendance.next_month');

    //　　　　　　　　- CSV download -
    Route::post('/admin/users/attendances/download', [AdminAttendanceController::class, 'downloadCsv'])->name('admin.users.attendance.download');

    //----------------- 申請一覧（管理者） ----------------------
    Route::get('/admin/requests', [AdminScheduleAdjustController::class, 'getStaffApplyList'])->name('admin.requests');

    //----------------- 修正申請承認（管理者） ----------------------
    Route::get('/admin/requests/{id}', [AdminScheduleAdjustController::class, 'showStaffApplyDetail'])->name('admin.requests.id');
    Route::post('/approve', [AdminScheduleAdjustController::class, 'approveNewSchedule'])->name('approve');
});

