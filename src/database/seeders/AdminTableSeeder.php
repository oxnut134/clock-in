<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Models\Admin;

class AdminTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /*
        $param = [
            'name' => 'AdminOne',
            'email' => 'admin1@test.com',
            'password' => Hash::make('abc12345'),
        ];
        DB::table('admins')->insert($param);
        $param = [
            'name' => 'AdminTwo',
            'email' => 'admin2@test.com',
            'password' => Hash::make('abc12345'),
        ];
        DB::table('admins')->insert($param);
*/
        Admin::create([
            'name' => 'AdminOne',
            'email' => 'admin1@test.com',
            'password' => bcrypt('abc12345'),
        ]);

        Admin::create([
            'name' => 'AdminTwo',
            'email' => 'admin2@test.com',
            'password' => bcrypt('abc12345'),
        ]);
    }
}
