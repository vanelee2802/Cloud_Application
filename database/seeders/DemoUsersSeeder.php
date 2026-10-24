<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DemoUsersSeeder extends Seeder
{
    public function run(): void
    {
        $password = env('DEMO_PASSWORD');

        if (!$password) {
            $this->command->error('DEMO_PASSWORD fehlt in der .env');
            return;
        }

        $accounts = [
            ['Demo Kunde', 'kunde@example.com', 'customer', null],
            ['Demo Mitarbeiter', 'mitarbeiter@example.com', 'employee', 1],
            ['Demo Admin', 'admin@example.com', 'admin', 1],
        ];

        foreach ($accounts as [$name, $email, $role, $studio]) {
            $user = User::updateOrCreate(
                ['email' => $email],
                ['name' => $name, 'password' => $password, 'role' => $role, 'nail_studio_id' => $studio]
            );
            $user->forceFill(['email_verified_at' => now()])->save();
        }
    }
}