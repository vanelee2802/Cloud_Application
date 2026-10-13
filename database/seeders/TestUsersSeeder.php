<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TestUsersSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => env('GOOGLE_CUSTOMER_EMAIL')],
            [
                'name' => 'Test Kunde',
                'password' => Str::random(24),
                'role' => 'customer',
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => env('GOOGLE_EMPLOYEE_EMAIL')],
            [
                'name' => 'Test Mitarbeiter',
                'password' => Str::random(24),
                'role' => 'employee',
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => env('GOOGLE_ADMIN_EMAIL')],
            [
                'name' => 'Test Admin',
                'password' => Str::random(24),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );
    }
}