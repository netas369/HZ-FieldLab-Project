<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('worldclassmaintenance'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Data analyst',
            'email' => 'data@example.com',
            'password' => bcrypt('dataanalysis'),
            'role' => 'user',
        ]);


        User::create([
            'name' => 'user',
            'email' => 'user@example.com',
            'password' => bcrypt('userpassword'),
            'role' => 'user',
        ]);
    }
}
