<?php

namespace App\Models;

use App\Enums\LoanStatus;
use App\Enums\Purpose;
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
        'payment_schedule',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:15,3',
            'status' => LoanStatus::class,
            'purpose' => Purpose::class,
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
}
