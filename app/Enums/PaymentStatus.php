<?php

namespace App\Enums;

use Filament\Support\Colors\Color;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum PaymentStatus: string implements HasColor, HasLabel
{
    case PENDING = 'Pending';
    case COMPLETED = 'Completed';
    case PARTIALLY_PAID = 'Partially Paid';
    case FAILED = 'Failed';

    public function getLabel(): ?string
    {
        return __($this->value);
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::PENDING => 'gray',
            self::PARTIALLY_PAID => Color::Yellow,
            self::COMPLETED => Color::Green,
            self::FAILED => 'danger',
        };
    }
}

//
