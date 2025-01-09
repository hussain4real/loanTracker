<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    /** @use HasFactory<\Database\Factories\PaymentFactory> */
    use HasFactory;

    protected $fillable = [
        'loan_id',
        'amount',
        'month',
        'payment_date',
        'payment_method',
        'received_bank',
        'payment_reference',
        'due_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:3',
            'payment_date' => 'date',
            'due_date' => 'date',
            'payment_method' => PaymentMethod::class,
            'status' => PaymentStatus::class,
        ];
    }

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }

    protected function isLate(): Attribute
    {
        return Attribute::make(
            get: function () {
                return $this->status === PaymentStatus::PENDING
                    && $this->due_date
                    && $this->due_date->isPast();
            }
        )->shouldCache();
    }

    protected function daysLate(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (! $this->isLate) {
                    return 0;
                }

                return $this->due_date->diffInDays(Carbon::now());
            }
        )->shouldCache();
    }

    protected function formattedAmount(): Attribute
    {
        return Attribute::make(
            get: fn () => number_format($this->amount, 2)
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
