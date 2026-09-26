<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $accounts = [
            config('seeding.admin'),
            config('seeding.user'),
        ];

        foreach ($accounts as $account) {
            if (! is_array($account) || blank($account['password'] ?? null)) {
                throw new RuntimeException(
                    'Set SEED_ADMIN_PASSWORD and SEED_USER_PASSWORD in .env before running the database seeder.'
                );
            }

            User::updateOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'password' => $account['password'],
                    'role' => $account['role'],
                    'status' => User::STATUS_ACTIVE,
                ]
            );
        }
    }
}
