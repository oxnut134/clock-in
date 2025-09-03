<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Job;
use App\Models\User;
use App\Models\Admin;
use App\Models\BreakTime;


use Carbon\Carbon;

class AllTablesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // テーブルデータ作成
        //---- Admins -------
        $admin = [];
        $admin = Admin::create([
            'name' => 'Admin1', //勤務外
            'email' => 'Admin1@test.com',
            'password' => bcrypt('abc12345'),
        ]);
        $admin->save();

        //---- User -------
        $users = [];
        $user = User::create([
            'name' => 'テスト一郎', //勤務外
            'email' => 'test1@test.com',
            'password' => bcrypt('abc12345'),
            'password_confirmation' => bcrypt('abc12345'),
            'email_verified_at' => now(),
        ]);

        $user = User::create([
            'name' => 'テスト二郎', //出勤中
            'email' => 'test2@test.com',
            'password' => bcrypt('abc12345'),
            'password_confirmation' => bcrypt('abc12345'),
            'email_verified_at' => now(),

        ]);
        $user = User::create([
            'name' => 'テスト三郎', //休憩中
            'email' => 'test3@test.com',
            'password' => bcrypt('abc12345'),
            'password_confirmation' => bcrypt('abc12345'),
            'email_verified_at' => now(),

        ]);
        $user = User::create([
            'name' => 'テスト四郎', //出勤中
            'email' => 'test4@test.com',
            'password' => bcrypt('abc12345'),
            'password_confirmation' => bcrypt('abc12345'),
            'email_verified_at' => now(),

        ]);
        $user = User::create([
            'name' => 'テスト五郎', //退勤済
            'email' => 'test5@test.com',
            'password' => bcrypt('abc12345'),
            'password_confirmation' => bcrypt('abc12345'),
            'email_verified_at' => now(),

        ]);

        $user->save();

//------------ Jobs and breaks(BreakTime)  ----------------------

        $users = User::all();
        foreach ($users as $user) {
            for ($i = 0; $i < 100; $i++) { // X日分のデータを生成
                $random = Rand(1, 5);
                switch ($random) {
                    case 2:
                        $job_status = "applied";
                        break;
                    case 4:
                        $job_status = "approved";
                        break;
                    default:
                        $job_status = "normal";
                        break;
                }
                $date = Carbon::create(Carbon::now()->year, 7, 1)->addDays($i); // 現在の年のm月d日からの日付で取得
                DB::table('jobs')->insert([
                    'user_id' => $user->id, //rand(1, 4),\\\
                    'date' => $date->format('Y-m-d'),
                    'day_of_week' => $date->format('D'),
                    'job_start' => Carbon::createFromTime(rand(8, 8), rand(30, 59))->format('H:i:s'), // ランダムな出勤時間
                    'job_finish' => Carbon::createFromTime(rand(18, 18), rand(0, 30))->format('H:i:s'), // ランダムな退勤時間
                    'job_status' => $job_status,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            $jobs = Job::where('user_id', $user->id)->get();
            foreach ($jobs as $job) { //Jobのレコード数分繰り返し
                DB::table('breaks')->insert([
                    'job_id' => $job->id, //+ 1,
                    'break_start' => Carbon::createFromTime(12, rand(0, 0))->format('H:i:s'), // ランダムな出勤時間
                    'break_finish' => Carbon::createFromTime(13, rand(0, 0))->format('H:i:s'), // ランダムな退勤時間
                    'break_status' => "normal",
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $r = rand(1, 5); //5回に1回2レコード目のbreakを生成
                if ($r == 6) {               //if内実行せず（２回目の休憩レコードなしで固定）
                    DB::table('breaks')->insert([
                        'job_id' => $job->id,
                        'break_start' => Carbon::createFromTime(13, rand(0, 0))->format('H:i:s'), // ランダムな出勤時間
                        'break_finish' => Carbon::createFromTime(13, rand(30, 30))->format('H:i:s'), // ランダムな退勤時間
                        'break_status' => "normal",
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}
