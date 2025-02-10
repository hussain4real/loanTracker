<?php

namespace App\Models;

use App\Enums\LoanStatus;
use App\Enums\PaymentStatus;
use App\Enums\Purpose;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy(\App\Observers\LoanObserver::class)]
class Loan extends Model
{
    /** @use HasFactory<\Database\Factories\LoanFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount',
        'purpose',
        'status',
        'approved_at',
        'due_date',
        'duration',
        'payment_schedule',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:3',
            'status' => LoanStatus::class,
            'purpose' => Purpose::class,
            'due_date' => 'date',
            'duration' => 'integer',
            'payment_schedule' => 'array',
            'approved_at' => 'date',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    // calculate outstanding balance
    protected function outstandingBalance(): Attribute
    {
        return Attribute::make(
            get: function () {
                return $this->amount - $this->payments()
                    ->where('status', PaymentStatus::COMPLETED)
                    ->sum('amount');
            }
        )->shouldCache();
    }

    // Calculate monthly installment
    protected function monthlyInstallment(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (! $this->duration || $this->duration === 0) {
                    return 0;
                }

                $amount = floatval($this->amount);
                $duration = intval($this->duration);

                return round($amount / $duration, 3);
            }
        )->shouldCache();
    }

    protected function completionPercentage(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->amount === 0) {
                    return 0;
                }

                return round(
                    ($this->payments()
                        ->where('status', PaymentStatus::COMPLETED)
                        ->sum('amount') / $this->amount) * 100,
                    2
                );
            }
        )->shouldCache();
    }

    protected function nextPaymentDate(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->status !== LoanStatus::APPROVED) {
                    return null;
                }

                // Get payment schedule
                $schedule = $this->payment_schedule ?? [];

                // Find the next pending payment from the schedule
                $nextPayment = collect($schedule)
                    ->where('status', PaymentStatus::PENDING->value)
                    ->sortBy('due_date')
                    ->first();

                return $nextPayment ? Carbon::parse($nextPayment['due_date']) : null;
            }
        )->shouldCache();
    }

    // // Calculate late fees, no interest rate
    // protected function lateFees(): Attribute
    // {
    //     return Attribute::make(
    //         get: function () {
    //             $lateFees = 0;
    //             $payments = $this->payments()
    //                 ->where('due_date', '<', now())
    //                 ->get();

    //             foreach ($payments as $payment) {
    //                 $lateFees += $payment->amount * 0.05; // 5% of pending payment
    //             }

    //             return $lateFees;

    //         }
    //     )->shouldCache();
    // }

    // Generate payment schedule
    public function generatePaymentSchedule(): array
    {
        if (! empty($this->payment_schedule)) {
            return $this->payment_schedule;
        }

        $schedule = [];
        $monthlyPayment = $this->monthly_installment;
        $startDate = Carbon::parse($this->approved_at);

        // Get all existing payments
        $allPayments = $this->payments()->get();
        $totalPaid = $allPayments->where('status', PaymentStatus::COMPLETED)->sum('amount');
        $remainingAmount = max(0, $this->amount - $totalPaid);
        $cumulativeOutstanding = 0;

        for ($i = 1; $i <= $this->duration; $i++) {
            $monthPaid = $allPayments
                ->where('month', Carbon::parse($startDate)->format('F'))
                ->where('status', PaymentStatus::COMPLETED)
                ->sum('amount');

            $monthOutstanding = max(0, $monthlyPayment - $monthPaid);
            $cumulativeOutstanding += $monthOutstanding;

            // Determine payment status
            if ($monthlyPayment == 0 || $monthPaid >= $monthlyPayment) {
                $status = PaymentStatus::COMPLETED->value;
            } else {
                $status = PaymentStatus::PENDING->value;
            }

            $schedule[] = [
                'month' => Carbon::parse($startDate)->format('F'),
                'amount' => $monthlyPayment,
                'amount_paid' => $monthPaid,
                'outstanding' => $monthOutstanding,
                'outstanding_till_date' => $cumulativeOutstanding,
                'status' => $status,
                'due_date' => $startDate->copy()->endOfMonth()->format('Y-m-d'),
            ];

            $startDate->addMonth();
        }

        $this->payment_schedule = $schedule;
        $this->save();

        return $schedule;
    }

    public function approve(): void
    {
        if ($this->status === LoanStatus::PENDING) {
            $this->status = LoanStatus::APPROVED;
            $this->approved_at = now();
            $this->save();

            // Generate payment schedule if it doesn't exist
            if (empty($this->payment_schedule)) {
                $this->generatePaymentSchedule();
            }
        }
    }

    // Update loan status based on payments and due dates
    public function updateLoanStatus(): void
    {
        $outstandingBalance = $this->outstanding_balance;
        $overduePayments = $this->payments()
            ->where('status', PaymentStatus::PENDING)
            ->where('due_date', '<', now())
            ->count();

        $this->status = match (true) {
            $outstandingBalance <= 0 => LoanStatus::COMPLETED,
            $overduePayments > 3 => LoanStatus::DEFAULTED,
            $overduePayments > 0 => LoanStatus::OVERDUE,
            $outstandingBalance > 0 => LoanStatus::PENDING,
            default => LoanStatus::APPROVED,
        };
        $this->save();
    }

    protected function amountPaid(): Attribute
    {
        return Attribute::make(
            get: function () {
                return $this->payments()
                    ->where('status', PaymentStatus::COMPLETED)
                    ->sum('amount');
            }
        )->shouldCache();
    }
}
