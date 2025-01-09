<?php

namespace Database\Seeders;

use App\Enums\LoanStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Loan;
use App\Models\Payment;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $loans = Loan::whereIn('status', [LoanStatus::APPROVED])->get();

        foreach ($loans as $loan) {
            $schedule = collect($loan->payment_schedule);
            $numberOfPayments = (int) ceil($schedule->count() / 2);

            foreach (range(1, $numberOfPayments) as $i) {
                $scheduledPayment = $schedule[$i - 1] ?? null;
                if (! $scheduledPayment) {
                    continue;
                }

                $status = $i <= $numberOfPayments
                    ? PaymentStatus::COMPLETED
                    : PaymentStatus::PENDING;

                Payment::factory()
                    ->fromSchedule()
                    ->state(function () {
                        $method = fake()->randomElement(PaymentMethod::cases());

                        return [
                            'payment_method' => $method->value,
                            'received_bank' => $method === PaymentMethod::CASH
                                ? null
                                : fake()->randomElement(['ABC Bank', 'XYZ Bank', 'City Bank']),
                            'payment_reference' => $method === PaymentMethod::CASH
                                ? null
                                : fake()->unique()->regexify('[A-Z0-9]{10}'),
                        ];
                    })
                    ->state(['status' => $status])
                    ->create(['loan_id' => $loan->id]);

                $updatedSchedule = $schedule->toArray();
                $updatedSchedule[$i - 1] = array_merge($scheduledPayment, [
                    'status' => $status->value,
                ]);

                // Update the payment_schedule in the loan record
                $loan->forceFill(['payment_schedule' => $updatedSchedule])->saveQuietly();
                $loan->refresh();
            }

            // Update loan status after processing payments
            $loan->updateLoanStatus();
        }
    }
}
