<?php

namespace Database\Seeders;

use App\Models\Household;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HouseholdSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create test household for spouse linking
        Household::create([
            'household_name' => 'Smith Family',
            'notes' => 'Test household for development - married couple with joint assets',
        ]);

        // Create another test household
        Household::create([
            'household_name' => 'Jones Family',
            'notes' => 'Test household for development - second family',
        ]);
    }
}
