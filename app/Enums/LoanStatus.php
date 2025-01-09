<?php

namespace App\Enums;

use Filament\Support\Colors\Color;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum LoanStatus: string implements HasColor, HasLabel
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

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::PENDING => 'gray',
            self::APPROVED => Color::Blue,
            self::REJECTED => 'danger',
            self::COMPLETED => Color::Green,
            self::CANCELLED => 'danger',
            self::OVERDUE => 'warning',
            self::DEFAULTED => 'danger',
        };
    }

    //
    public function getColorClasses(): array
    {
        $color = match ($this->getColor()) {
            'gray' => [
                'text' => 'text-gray-700 dark:text-gray-300',
                'bg' => 'bg-gray-100 dark:bg-gray-900',
            ],
            Color::Blue => [
                'text' => 'text-blue-700 dark:text-blue-300',
                'bg' => 'bg-blue-100 dark:bg-blue-900',
            ],
            'danger' => [
                'text' => 'text-red-700 dark:text-red-300',
                'bg' => 'bg-red-100 dark:bg-red-900',
            ],
            Color::Green => [
                'text' => 'text-green-700 dark:text-green-300',
                'bg' => 'bg-green-100 dark:bg-green-900',
            ],
            'warning' => [
                'text' => 'text-yellow-700 dark:text-yellow-300',
                'bg' => 'bg-yellow-100 dark:bg-yellow-900',
            ],
            default => [
                'text' => 'text-gray-700 dark:text-gray-300',
                'bg' => 'bg-gray-100 dark:bg-gray-900',
            ],
        };

        return $color;
    }
}
