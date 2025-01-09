<?php

namespace Database\Factories;

use App\Enums\LoanStatus;
use App\Enums\Purpose;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Loan>
 */
class LoanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = \App\Models\Loan::class;

    public function definition(): array
    {
        $approved_at = $this->faker->dateTimeBetween('-1 month', 'now');
        $purpose = $this->faker->randomElement(Purpose::cases());

        return [
            'user_id' => \App\Models\User::factory(),
            'amount' => $this->faker->randomFloat(3, 1000, 10000),
            'purpose' => $purpose,
            'status' => LoanStatus::APPROVED,
            'approved_at' => $this->faker->dateTimeBetween('now', 'now'),
            'due_date' => $this->faker->dateTimeBetween($approved_at, '+1 year'),
            'duration' => $this->faker->numberBetween(6, 12),
            // 'payment_schedule' => [
            //     'monthly_payment' => $this->faker->randomFloat(3, 100, 1000),
            //     'payment_start_date' => $approved_at,
            // ],
        ];
    }
}
