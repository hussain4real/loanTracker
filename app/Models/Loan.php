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

                $pendingMonth = collect($this->payment_schedule)
                    ->where('status', PaymentStatus::PENDING->value)
                    ->sortBy('sequence_number')
                    ->first();

                return $pendingMonth ? Carbon::parse($pendingMonth['due_date']) : null;
            }
        )->shouldCache();
    }

    // Generate payment schedule
    public function generatePaymentSchedule(): array
    {
        $schedule = [];
        $monthlyPayment = number_format($this->monthly_installment, 3, '.', '');
        $startDate = Carbon::parse($this->approved_at);

        // Get all completed payments indexed by sequence number
        $completedPayments = $this->payments()
            ->where('status', PaymentStatus::COMPLETED)
            ->get()
            ->groupBy('month') // month field now stores sequence number
            ->map(function ($payments) {
                return number_format($payments->sum('amount'), 3, '.', '');
            })
            ->toArray();

        $cumulativeOutstanding = 0;

        // Generate schedule for the loan duration
        for ($sequence = 1; $sequence <= $this->duration; $sequence++) {
            $monthPaid = number_format((float)($completedPayments[$sequence] ?? 0), 3, '.', '');
            $monthOutstanding = number_format(max(0, $monthlyPayment - $monthPaid), 3, '.', '');
            $cumulativeOutstanding = number_format($cumulativeOutstanding + $monthOutstanding, 3, '.', '');

            // Determine payment status
            $status = match (true) {
                bccomp($monthPaid, $monthlyPayment, 3) >= 0 => PaymentStatus::COMPLETED->value,
                bccomp($monthPaid, '0', 3) > 0 => PaymentStatus::PARTIALLY_PAID->value,
                default => PaymentStatus::PENDING->value
            };

            $schedule[] = [
                'month' => $startDate->format('F'),
                'sequence_number' => $sequence,
                'year' => $startDate->format('Y'),
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
        $this->saveQuietly();

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

    public function getMonthBySequence(int $sequenceNumber): ?array
    {
        if (!$this->payment_schedule) {
            return null;
        }

        return collect($this->payment_schedule)
            ->firstWhere('sequence_number', $sequenceNumber);
    }

    public function getPendingMonths(): array
    {
        if (!$this->payment_schedule) {
            return [];
        }

        return collect($this->payment_schedule)
            ->where('status', PaymentStatus::PENDING->value)
            ->sortBy('sequence_number')
            ->values()
            ->toArray();
    }

    public function getNextPaymentSequence(): ?int
    {
        $pendingMonth = collect($this->payment_schedule)
            ->where('status', PaymentStatus::PENDING->value)
            ->sortBy('sequence_number')
            ->first();

        return $pendingMonth ? $pendingMonth['sequence_number'] : null;
    }
}
