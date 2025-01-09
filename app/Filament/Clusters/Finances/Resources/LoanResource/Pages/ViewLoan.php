<?php

namespace App\Filament\Clusters\Finances\Resources\LoanResource\Pages;

use App\Filament\Clusters\Finances\Resources\LoanResource;
use Filament\Actions;
use Filament\Infolists\Components\View;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewLoan extends ViewRecord
{
    protected static string $resource = LoanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            LoanResource\Widgets\LoanOverview::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                View::make('filament.infolists.loan-details')
                    ->columnSpanFull(),
            ]);
    }
}
