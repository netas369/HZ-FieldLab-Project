<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
{
    User::updateOrCreate(
        ['email' => 'admin@example.com'],  // ← Find by email
        [
            'name' => 'Admin',
            'password' => bcrypt('worldclassmaintenance'),
            'role' => 'admin',
        ]  // ← Update/Create these fields
    );
    
    User::updateOrCreate(
        ['email' => 'data@example.com'],
        [
            'name' => 'Data analyst',
            'password' => bcrypt('dataanalysis'),
            'role' => 'data_analyst',
        ]
    );
    
    User::updateOrCreate(
        ['email' => 'user@example.com'],
        [
            'name' => 'user',
            'password' => bcrypt('userpassword'),
            'role' => 'user',
        ]
    );
}
}
