<?php

namespace App\Filament\Clusters\Finances\Resources\LoanResource\Pages;

use App\Filament\Clusters\Finances\Resources\LoanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLoans extends ListRecords
{
    protected static string $resource = LoanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
