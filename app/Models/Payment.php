<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy(\App\Observers\PaymentObserver::class)]
class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_id',
        'amount',
        'month', // Stores sequence number (1-N based on loan duration)
        'payment_date',
        'payment_method',
        'received_bank',
        'payment_reference',
        'due_date',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:3',
        'payment_date' => 'date',
        'due_date' => 'date',
        'payment_method' => PaymentMethod::class,
        'status' => PaymentStatus::class,
        'month' => 'integer',
    ];

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }

    protected function monthName(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->loan || !$this->month) {
                    return null;
                }

                $paymentEntry = $this->loan->getMonthBySequence($this->month);
                return $paymentEntry ? $paymentEntry['month'] : null;
            }
        )->shouldCache();
    }

    protected function isLate(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->status === PaymentStatus::PENDING && $this->due_date?->isPast()
        )->shouldCache();
    }

    protected function daysLate(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->isLate ? $this->due_date->diffInDays(Carbon::now()) : 0
        )->shouldCache();
    }

    protected function formattedAmount(): Attribute
    {
        return Attribute::make(
            get: fn() => number_format($this->amount, 2)
        );
    }

    public function markAsCompleted(): void
    {
        $this->status = PaymentStatus::COMPLETED;
        $this->payment_date = now();
        $this->save();

        // Update loan status after payment
        $this->loan->updateLoanStatus();
    }
}
