<?php

namespace Database\Factories;

use App\Enums\Month;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Loan;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $loan = Loan::inRandomOrder()->first();
        $paymentSchedule = $loan->payment_schedule ?? [];

        // Get a random scheduled payment if available
        $scheduledPayment = ! empty($paymentSchedule)
            ? collect($paymentSchedule)->random()
            : null;

        $paymentMethod = $this->faker->randomElement(PaymentMethod::cases());

        return [
            'loan_id' => $loan->id,
            'amount' => $scheduledPayment ? $scheduledPayment['amount'] : $loan->amount,
            'month' => $scheduledPayment ? $scheduledPayment['month'] : $this->faker->randomElement(Month::cases()),
            'payment_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'payment_method' => $paymentMethod,
            'received_bank' => $paymentMethod === PaymentMethod::CASH ? null : $this->faker->randomElement(['GTBank', 'Zenith Bank', 'First Bank', 'Keystone Bank']),
            'payment_reference' => $paymentMethod === PaymentMethod::CASH ? null : $this->faker->unique()->regexify('[A-Z0-9]{10}'),
            'due_date' => $scheduledPayment ? $scheduledPayment['due_date'] : $this->faker->dateTimeBetween('now', '+1 month'),
            'status' => $this->faker->randomElement(PaymentStatus::cases()),

        ];
    }

    // State for specific payment methods
    public function withPaymentMethod(PaymentMethod $method): static
    {
        return $this->state(function (array $attributes) use ($method) {
            return [
                'payment_method' => $method->value,
                'received_bank' => $method === PaymentMethod::CASH ? null : $attributes['received_bank'],
                'payment_reference' => $method === PaymentMethod::CASH ? null : $attributes['payment_reference'],
            ];
        });
    }

    // State for cash payments
    public function cash(): static
    {
        return $this->withPaymentMethod(PaymentMethod::CASH);
    }

    // State for bank transfer payments
    public function bankTransfer(): static
    {
        return $this->withPaymentMethod(PaymentMethod::BANK_TRANSFER);
    }

    // State for cheque payments
    public function cheque(): static
    {
        return $this->withPaymentMethod(PaymentMethod::CHEQUE);
    }

    // State to ensure payment matches a scheduled payment
    public function fromSchedule(): static
    {
        return $this->state(function (array $attributes) {
            $loan = Loan::find($attributes['loan_id']);
            $schedule = $loan->payment_schedule ?? [];

            if (empty($schedule)) {
                return $attributes;
            }

            $scheduledPayment = collect($schedule)->random();

            return [
                'amount' => $scheduledPayment['amount'],
                'month' => $scheduledPayment['month'],
                'due_date' => $scheduledPayment['due_date'],
            ];
        });
    }

    // State for completed payments
    public function completed(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => PaymentStatus::COMPLETED,
                'payment_date' => $this->faker->dateTimeBetween(
                    '-1 month',
                    $attributes['due_date']
                ),
            ];
        });
    }

    // State for pending payments
    public function pending(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => PaymentStatus::PENDING,
                'payment_date' => null,
            ];
        });
    }
}
