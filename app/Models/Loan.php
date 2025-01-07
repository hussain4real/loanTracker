<?php

namespace App\Models;

use App\Enums\LoanStatus;
use App\Enums\PaymentStatus;
use App\Enums\Purpose;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'payment_schedule',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:15,3',
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

    public function paymnets()
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
                return $this->amount / $this->duration ?? 12;
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
        $schedule = [];
        $monthlyPayment = $this->monthly_installment;
        $startDate = Carbon::parse($this->approved_at);

        for ($i = 1; $i <= $this->duration; $i++) {
            $schedule[] = [
                'month' => Carbon::parse($startDate)->format('F'),
                'due_date' => $startDate->copy()->endOfMonth()->format('Y-m-d'),
                'amount' => $monthlyPayment,
                'status' => PaymentStatus::PENDING->value,
            ];
            $startDate->addMonth();
        }

        $this->payment_schedule = $schedule;
        $this->save();

        return $schedule;
    }

    // Update loan status based on payments and due dates
    public function updateLoanStatus(): void
    {
        $pendingPayments = $this->payments()
            ->where('status', PaymentStatus::PENDING)
            ->count();

        $overduePayments = $this->payments()
            ->where('status', PaymentStatus::PENDING)
            ->where('due_date', '<', now())
            ->count();
        $this->status = match (true) {
            $pendingPayments === 0 => LoanStatus::COMPLETED,
            $overduePayments > 0 => LoanStatus::OVERDUE,
            $overduePayments > 3 => LoanStatus::DEFAULTED,
            default => LoanStatus::ACTIVE,
        };
        $this->save();
    }
}
