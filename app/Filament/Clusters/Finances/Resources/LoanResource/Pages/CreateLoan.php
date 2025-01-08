<?php

namespace App\Filament\Clusters\Finances\Resources\LoanResource\Pages;

use App\Filament\Clusters\Finances\Resources\LoanResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLoan extends CreateRecord
{
    protected static string $resource = LoanResource::class;

    // protected function beforeCreate(): void
    // {
    //     dd($this->data);
    // }
    protected function afterCreate(): void
    {
        dd($this->record);
    }
}
