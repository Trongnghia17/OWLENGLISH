<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Create Admin (role 0)
        User::create([
            'name' => 'Admin',
            'email' => 'admin@owlenglish.com',
            'password' => Hash::make('123456'),
            'role' => User::ROLE_ADMIN,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        // Create Teacher (role 1)
        User::create([
            'name' => 'Teacher',
            'email' => 'teacher@owlenglish.com',
            'password' => Hash::make('123456'),
            'role' => User::ROLE_TEACHER,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        // Create Assistant (role 2)
        User::create([
            'name' => 'Assistant',
            'email' => 'assistant@owlenglish.com',
            'password' => Hash::make('123456'),
            'role' => User::ROLE_ASSISTANT,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        // Create Student (role 3)
        User::create([
            'name' => 'Student',
            'email' => 'student@owlenglish.com',
            'password' => Hash::make('123456'),
            'role' => User::ROLE_STUDENT,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
    }
}