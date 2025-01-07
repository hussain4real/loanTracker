<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum Purpose: string implements HasLabel
{
    case BUSINESS = 'Business';

    case EDUCATION = 'Education';
    case HOUSE = 'House';
    case MEDICAL = 'Medical';
    case PERSONAL = 'Personal';
    case VEHICLE = 'Vehicle';
    case OTHER = 'Other';

    public function getLabel(): ?string
    {
        return __($this->value);
    }
}
