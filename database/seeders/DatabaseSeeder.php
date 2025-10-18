<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Run in order: Households first, then Users, then Admin
        $this->call([
            HouseholdSeeder::class,
            TestUsersSeeder::class,
            AdminUserSeeder::class,
        ]);
    }
}
