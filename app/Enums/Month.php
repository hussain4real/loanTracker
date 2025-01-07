<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum Month: string implements HasLabel
{
    case JAN = 'January';
    case FEB = 'February';
    case MAR = 'March';
    case APR = 'April';
    case MAY = 'May';
    case JUN = 'June';
    case JUL = 'July';
    case AUG = 'August';
    case SEP = 'September';
    case OCT = 'October';
    case NOV = 'November';
    case DEC = 'December';

    public function getLabel(): ?string
    {
        return __($this->value);
    }
}
