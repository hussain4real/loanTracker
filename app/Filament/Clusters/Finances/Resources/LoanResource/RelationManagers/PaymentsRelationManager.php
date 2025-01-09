<?php

namespace App\Filament\Clusters\Finances\Resources\LoanResource\RelationManagers;

use App\Enums\Month;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('amount')
                    ->required()
                    ->numeric(),
                Forms\Components\Select::make('month')
                    ->options(function () {
                        $loan = $this->getOwnerRecord();
                        $paymentSchedule = $loan->payment_schedule ?? [];
                        // get the first month in the payment schedule where the status is pending
                        $month = collect($paymentSchedule)->firstWhere('status', PaymentStatus::PENDING);

                        return $month ? [$month['month'] => $month['month']] : Month::class;
                    })
                    ->default(function () {
                        $loan = $this->getOwnerRecord();
                        $paymentSchedule = $loan->payment_schedule ?? [];
                        // get the first month in the payment schedule where the status is pending
                        $month = collect($paymentSchedule)->firstWhere('status', PaymentStatus::PENDING);

                        return $month ? $month['month'] : null;
                    }),
                Forms\Components\Select::make('payment_method')
                    ->options(PaymentMethod::class),
                Forms\Components\TextInput::make('received_bank'),
                Forms\Components\TextInput::make('payment_reference'),
                Forms\Components\Datepicker::make('due_date')
                    ->default(function () {
                        $loan = $this->getOwnerRecord();
                        $paymentSchedule = $loan->payment_schedule ?? [];
                        // get the first month in the payment schedule where the status is pending
                        $month = collect($paymentSchedule)->firstWhere('status', PaymentStatus::PENDING);

                        return $month ? $month['due_date'] : null;
                    }),
                Forms\Components\ToggleButtons::make('status')
                    ->options(PaymentStatus::class)
                    ->inline()
                    ->default(PaymentStatus::COMPLETED),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('id'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
                Tables\Actions\AssociateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DissociateAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DissociateBulkAction::make(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
