<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            [
                'name' => 'john',
                'father_name' => 'Smith',
                'email' => 'johnadmin@gmail.com',
                'cnic' => '36605-5257895-5',
                'phone' => '+923057642411',
                'employee_code' => '123',
                'role_id' => '1',
                'address' => 'Street 11, Y-Block,DHA Phase 3',
                'status' => '1',
                'password' => '$2y$10$cKLcRZ4xcGLELbiX1AgAHeomBcE2DTeLVKH9tgbCYsDw4/9Rj6EdW', //password
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ]);
    }
}
