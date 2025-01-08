<?php

namespace App\Enums;

use Filament\Support\Colors\Color;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum LoanStatus: string implements HasColor, HasLabel
{
    case PENDING = 'Pending';
    case APPROVED = 'Approved';
    case ACTIVE = 'Active';
    case REJECTED = 'Rejected';
    case COMPLETED = 'Completed';
    case CANCELLED = 'Cancelled';
    case OVERDUE = 'Overdue';
    case DEFAULTED = 'Defaulted';

    public function getLabel(): ?string
    {
        return __($this->value);
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::PENDING => 'gray',
            self::APPROVED => Color::Blue,
            self::ACTIVE => Color::Lime,
            self::REJECTED => 'danger',
            self::COMPLETED => Color::Green,
            self::CANCELLED => 'danger',
            self::OVERDUE => 'warning',
            self::DEFAULTED => 'danger',
        };
    }
}
