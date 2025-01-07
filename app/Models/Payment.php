<?php

namespace App\Models;

use App\Enums\PaymentStatus;
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
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:15,3',
            'payment_date' => 'date',
            'due_date' => 'date',

            'status' => PaymentStatus::class,
        ];
    }

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }
}
