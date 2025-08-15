<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

use Carbon\Carbon;

class JobTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($i = 0; $i < 95; $i++) { // 30日分のデータを生成
            //$date = Carbon::now()->startOfMonth()->addDays($i); // 月初めから日付を増加
            $date = Carbon::create(Carbon::now()->year, 6, 30)->addDays($i); // 現在の年の5月1日から日付を増加
            DB::table('jobs')->insert([
                'user_id' => 1, //rand(1, 4),
                'date' => $date->format('Y-m-d'),
                'day_of_week' => $date->format('D'),
                'job_start' => Carbon::createFromTime(rand(8, 8), rand(30, 59))->format('H:i:s'), // ランダムな出勤時間
                'job_finish' => Carbon::createFromTime(rand(18, 18), rand(0, 30))->format('H:i:s'), // ランダムな退勤時間
                'job_status' => "normal",
                //'break_time' => '01:00:00', // 固定の休憩時間
                //'total_time' => '08:00:00', // 固定の合計時間
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('jobs')->insert([
                'user_id' => 2, //rand(1, 4),
                'date' => $date->format('Y-m-d'),
                'day_of_week' => $date->format('D'),
                'job_start' => Carbon::createFromTime(rand(8, 8), rand(40, 59))->format('H:i:s'), // ランダムな出勤時間
                'job_finish' => Carbon::createFromTime(rand(18, 18), rand(0, 30))->format('H:i:s'), // ランダムな退勤時間
                'job_status' => "normal",
                //'break_time' => '01:00:00', // 固定の休憩時間
                //'total_time' => '08:00:00', // 固定の合計時間
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('jobs')->insert([
                'user_id' => 3, //rand(1, 4),
                'date' => $date->format('Y-m-d'),
                'day_of_week' => $date->format('D'),
                'job_start' => Carbon::createFromTime(rand(8, 8), rand(40, 59))->format('H:i:s'), // ランダムな出勤時間
                'job_finish' => Carbon::createFromTime(rand(18, 18), rand(0, 30))->format('H:i:s'), // ランダムな退勤時間
                'job_status' => "normal",
                //'break_time' => '01:00:00', // 固定の休憩時間
                //'total_time' => '08:00:00', // 固定の合計時間
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
