<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum LoanStatus: string implements HasLabel
{
    case PENDING = 'Pending';
    case APPROVED = 'Approved';
    case REJECTED = 'Rejected';
    case COMPLETED = 'Completed';
    case CANCELLED = 'Cancelled';
    case OVERDUE = 'Overdue';
    case DEFAULTED = 'Defaulted';

    public function getLabel(): ?string
    {
        return __($this->value);
    }
}
