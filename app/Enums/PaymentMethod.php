<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum PaymentMethod: string implements HasLabel
{
    case CASH = 'Cash';
    case CHEQUE = 'Cheque';
    case BANK_TRANSFER = 'Bank Transfer';
    case MOBILE_MONEY = 'Mobile Money';
    // case CARD = 'Card';
    case OTHER = 'Other';

    public function getLabel(): ?string
    {
        return __($this->value);
    }
}
