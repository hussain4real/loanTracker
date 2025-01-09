<?php

namespace App\Filament\Pages\Tenancy;

use App\Models\Team;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Pages\Tenancy\RegisterTenant;
use Illuminate\Database\Eloquent\Model;

class RegisterTeam extends RegisterTenant
{
    public static function getLabel(): string
    {
        return 'Register Team';
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Hidden::make('user_id')
                ->default(auth()->id()),
            TextInput::make('name')
                ->label('Name')
                ->required(),
            Toggle::make('personal_team')
                ->label('Personal Team'),
        ]);
    }

    protected function handleRegistration(array $data): Model
    {

        $team = Team::create($data);
        $team->members()->attach(auth()->user());

        return $team;
    }
}
