<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UKLifeExpectancySeeder extends Seeder
{
    /**
     * Seed UK Life Expectancy Tables
     *
     * Data source: ONS National Life Tables 2020-2022
     * https://www.ons.gov.uk/peoplepopulationandcommunity/birthsdeathsandmarriages/lifeexpectancies
     */
    public function run(): void
    {
        // Clear existing data
        DB::table('uk_life_expectancy_tables')->truncate();

        // ONS 2020-2022 Life Expectancy Data (simplified for ages 0-100)
        // Format: [age, male_life_expectancy, female_life_expectancy]
        $lifeExpectancyData = [
            [0, 78.70, 82.80],
            [20, 59.20, 63.20],
            [25, 54.40, 58.30],
            [30, 49.60, 53.40],
            [35, 44.80, 48.50],
            [40, 40.10, 43.70],
            [45, 35.40, 38.90],
            [50, 30.80, 34.20],
            [55, 26.40, 29.60],
            [60, 22.20, 25.10],
            [65, 18.30, 20.90],
            [70, 14.60, 16.80],
            [75, 11.20, 13.10],
            [80, 8.30, 9.80],
            [85, 5.90, 7.00],
            [90, 4.10, 4.90],
            [95, 2.80, 3.30],
            [100, 1.90, 2.20],
        ];

        $records = [];
        $now = now();

        foreach ($lifeExpectancyData as [$age, $maleLE, $femaleLE]) {
            // Male
            $records[] = [
                'age' => $age,
                'gender' => 'male',
                'life_expectancy_years' => $maleLE,
                'table_version' => 'ONS_2020_2022',
                'data_year' => 2022,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            // Female
            $records[] = [
                'age' => $age,
                'gender' => 'female',
                'life_expectancy_years' => $femaleLE,
                'table_version' => 'ONS_2020_2022',
                'data_year' => 2022,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('uk_life_expectancy_tables')->insert($records);

        $this->command->info('UK Life Expectancy Tables seeded successfully.');
    }
}
