<?php

namespace App\Filament\Pages;

use App\Enums\Month;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;

class Dashboard extends \Filament\Pages\Dashboard
{
    use HasFiltersForm;

    public function getColumns(): int|string|array
    {
        return 2;
    }

    // public function filtersForm(Form $form): Form
    // {
    //     return $form
    //         ->schema([
    //             Section::make()
    //                 ->schema([
    //                     Select::make('month')
    //                         ->label('Month')
    //                         ->options(Month::class),
    //                 ]),
    //         ]);
    // }
}
