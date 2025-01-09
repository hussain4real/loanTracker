<?php

namespace App\Filament\Clusters\Finances\Resources\LoanResource\RelationManagers;

use App\Enums\Month;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('amount')
                    ->default(function () {
                        $loan = $this->getOwnerRecord();
                        $paymentSchedule = $loan->payment_schedule ?? [];

                        if (empty($paymentSchedule)) {
                            return 0;
                        }

                        // Get first pending payment from schedule
                        $pendingPayment = collect($paymentSchedule)
                            ->firstWhere('status', PaymentStatus::PENDING->value);

                        if (! $pendingPayment) {
                            return 0;
                        }

                        // Get total paid amount for this month
                        $paidAmount = $loan->payments()
                            ->where('month', $pendingPayment['month'])
                            ->where('status', PaymentStatus::COMPLETED->value)
                            ->sum('amount');

                        // Return remaining amount for the month
                        return max(0, $pendingPayment['amount'] - $paidAmount);
                    })
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
                    ->options(PaymentMethod::class)
                    ->live(onBlur: true),
                Forms\Components\TextInput::make('received_bank')
                    ->visible(function (Get $get) {
                        return $get('payment_method') !== PaymentMethod::CASH->value;
                    }),
                Forms\Components\TextInput::make('payment_reference'),
                Forms\Components\Datepicker::make('due_date')
                    ->default(function () {
                        $loan = $this->getOwnerRecord();
                        $paymentSchedule = $loan->payment_schedule ?? [];
                        // get the first month in the payment schedule where the status is pending
                        $month = collect($paymentSchedule)->firstWhere('status', PaymentStatus::PENDING);

                        return $month ? $month['due_date'] : null;
                    }),
                Hidden::make('payment_date')
                    ->default(now()->format('Y-m-d'))
                    ->dehydrated(),

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
            ->recordClasses(fn (Model $record) => match ($record->status) {
                PaymentStatus::PENDING => 'border-s-2 border-gray-600 dark:border-gray-300',
                PaymentStatus::FAILED => 'border-s-2 border-orange-600 dark:border-orange-300',
                PaymentStatus::COMPLETED => 'border-s-2 border-green-600 dark:border-green-300',
                default => null,
            })
            ->columns([
                // Tables\Columns\TextColumn::make('id'),
                Tables\Columns\TextColumn::make('amount')
                    ->numeric(),
                Tables\Columns\TextColumn::make('month'),
                Tables\Columns\TextColumn::make('payment_date')
                    ->date(),
                Tables\Columns\TextColumn::make('payment_method'),
                Tables\Columns\TextColumn::make('received_bank')
                    ->hidden(function (TextColumn $column, Get $get) {

                        $state = $column->getState();

                        return $state !== PaymentMethod::BANK_TRANSFER || $state !== PaymentMethod::CHEQUE || $state !== PaymentMethod::MOBILE_MONEY || $state->isEmpty();
                    }),
                Tables\Columns\TextColumn::make('payment_reference')
                    ->limit(50),
                Tables\Columns\TextColumn::make('due_date')
                    ->date(),
                Tables\Columns\TextColumn::make('status')
                    ->badge(),

            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->before(function (Tables\Actions\CreateAction $action, array $data) {
                        $loan = $this->getOwnerRecord();
                        $paymentSchedule = $loan->payment_schedule ?? [];
                        // compare the month of the first pending payment in the schedule with the  month in the data
                        $month = collect($paymentSchedule)->firstWhere('status', PaymentStatus::PENDING);
                        if ($month && $month['month'] === $data['month']) {

                            // update the payment schedule to mark the payment as completed if the data amount is equal to the pending amount in the schedule
                            $updatedSchedule = collect($paymentSchedule)->map(function ($payment) use ($data) {
                                if ($payment['month'] === $data['month']) {
                                    return array_merge($payment, [
                                        'status' => PaymentStatus::COMPLETED->value,
                                    ]);
                                }

                                return $payment;
                            })->toArray();

                            $loan->forceFill(['payment_schedule' => $updatedSchedule])->saveQuietly();
                        } else {
                            dd('Payment cannot be created');
                        }
                    }),
                // Tables\Actions\AssociateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                // Tables\Actions\DissociateAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DissociateBulkAction::make(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
