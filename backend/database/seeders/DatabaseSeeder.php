<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create users first
        User::factory()
            ->count(100)
            ->create();

        // Create events using the existing users
        $this->call([
            EventSeeder::class,
        ]);
    }
}