<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;

class Finances extends Cluster
{
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    public static function getNavigationLabel(): string
    {
        return __('Finances');
    }

    public static function getClusterBreadcrumb(): ?string
    {
        return __('Finances');
    }
}
