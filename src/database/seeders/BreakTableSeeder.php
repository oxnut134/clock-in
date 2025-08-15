<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

use Carbon\Carbon;

class BreakTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($i = 0; $i < 3*95; $i+=3) { // 30日分のデータを生成
            //$date = Carbon::now()->startOfMonth()->addDays($i); // 月初めから日付を増加
            //$date = Carbon::create(Carbon::now()->year, 6, 1)->addDays($i);
            //$j = rand(1, 5);
            for ($j = 0; $j < 3; $j++) {
                DB::table('breaks')->insert([
                    'job_id' => $i + $j +1,
                    'break_start' => Carbon::createFromTime(12, rand(0, 0))->format('H:i:s'), // ランダムな出勤時間
                    'break_finish' => Carbon::createFromTime(13, rand(0, 0))->format('H:i:s'), // ランダムな退勤時間
                    'break_status' => "normal",
                    //'break_time' => '01:00:00', // 固定の休憩時間
                    //'total_time' => '08:00:00', // 固定の合計時間
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $r = rand(1, 5);
                if ($r == 1) {
                    DB::table('breaks')->insert([
                        'job_id' => $i + $j +1  ,
                        'break_start' => Carbon::createFromTime(13, rand(0, 0))->format('H:i:s'), // ランダムな出勤時間
                        'break_finish' => Carbon::createFromTime(13, rand(30, 30))->format('H:i:s'), // ランダムな退勤時間
                        'break_status' => "normal",
                        //'break_time' => '01:00:00', // 固定の休憩時間
                        //'total_time' => '08:00:00', // 固定の合計時間
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}
