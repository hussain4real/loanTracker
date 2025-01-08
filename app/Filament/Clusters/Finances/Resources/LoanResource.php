<?php

namespace App\Filament\Clusters\Finances\Resources;

use App\Enums\LoanStatus;
use App\Enums\Purpose;
use App\Filament\Clusters\Finances;
use App\Filament\Clusters\Finances\Resources\LoanResource\Pages;
use App\Models\Loan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LoanResource extends Resource
{
    protected static ?string $model = Loan::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $cluster = Finances::class;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship(name: 'user', titleAttribute: 'name', ignoreRecord: true)
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->required(),
                        Forms\Components\Hidden::make('email')
                            ->afterStateUpdated(function ($state, Get $get) {

                                return $get('name').rand(1000, 9999).'@loanee.com';
                            }),
                        Forms\Components\Hidden::make('password')
                            ->default('password'),
                        Forms\Components\TextInput::make('id_number')
                            ->label('ID/Passport Number'),
                        Forms\Components\TextInput::make('phone_number')
                            ->label('Phone Number'),
                        Forms\Components\TextInput::make('address')
                            ->label('Physical Address'),

                    ]),
                Forms\Components\TextInput::make('amount')
                    ->numeric(),
                Forms\Components\Select::make('purpose')
                    ->options(Purpose::class),
                Forms\Components\Select::make('status')
                    ->options(LoanStatus::class),
                Forms\Components\DatePicker::make('approved_at'),
                Forms\Components\DatePicker::make('due_date'),
                Forms\Components\TextInput::make('duration')
                    ->numeric()
                    ->suffix('months'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('purpose')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->searchable(),
                Tables\Columns\TextColumn::make('approved_at')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('due_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('duration')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLoans::route('/'),
            'create' => Pages\CreateLoan::route('/create'),
            'view' => Pages\ViewLoan::route('/{record}'),
            'edit' => Pages\EditLoan::route('/{record}/edit'),
        ];
    }
}
