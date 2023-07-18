<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::updateOrCreate([
            'email' => 'admin@gmail.com'
        ], [
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'email'=>'admin@gmail.com',
            'password' => bcrypt('admin123'),
            'role' => 'Super Admin'
        ]);
    }
}
