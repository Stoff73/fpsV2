<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DBPension>
 */
class DBPensionFactory extends Factory
{
    protected $model = \App\Models\DBPension::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'scheme_name' => $this->faker->randomElement([
                'NHS Pension Scheme',
                'Teachers\' Pension',
                'Civil Service Pension',
                'Local Government Pension Scheme',
                'Police Pension Scheme',
            ]),
            'scheme_type' => $this->faker->randomElement(['final_salary', 'career_average', 'public_sector']),
            'accrued_annual_pension' => $this->faker->randomFloat(2, 5000, 40000),
            'pensionable_service_years' => $this->faker->randomFloat(2, 5, 35),
            'pensionable_salary' => $this->faker->randomFloat(2, 25000, 80000),
            'normal_retirement_age' => $this->faker->randomElement([60, 65, 66, 67, 68]),
            'revaluation_method' => $this->faker->randomElement([
                'CPI indexation',
                'RPI indexation',
                'Treasury Order',
                'Fixed rate',
            ]),
            'spouse_pension_percent' => $this->faker->randomElement([50.0, 66.67, 100.0]),
            'lump_sum_entitlement' => $this->faker->randomFloat(2, 0, 100000),
            'inflation_protection' => $this->faker->randomElement(['cpi', 'rpi', 'fixed', 'none']),
        ];
    }
}
