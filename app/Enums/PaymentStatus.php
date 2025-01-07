<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum PaymentStatus: string implements HasLabel
{
    case PENDING = 'Pending';
    case COMPLETED = 'Completed';
    case FAILED = 'Failed';

    public function getLabel(): ?string
    {
        return __($this->value);
    }
}

//
