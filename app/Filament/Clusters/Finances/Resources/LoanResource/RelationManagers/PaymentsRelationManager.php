<?php

namespace App\Filament\Clusters\Finances\Resources\LoanResource\RelationManagers;

use App\Enums\Month;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('Payments');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Payment');
    }

    public static function getModelLabel(): string
    {
        return __('Payment');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('amount')
                    ->label(__('Amount'))
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
                    ->label(__('Month'))
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
                    ->label(__('Payment Method'))
                    ->options(PaymentMethod::class)
                    ->live(onBlur: true),
                Forms\Components\TextInput::make('received_bank')
                    ->label(__('Received Bank'))
                    ->visible(function (Get $get) {
                        return $get('payment_method') !== PaymentMethod::CASH->value;
                    }),
                Forms\Components\TextInput::make('payment_reference')
                    ->label(__('Payment Reference'))
                    ->visible(function (Get $get) {
                        return $get('payment_method') !== PaymentMethod::CASH->value;
                    }),
                DatePicker::make('due_date')
                    ->label(__('Due Date'))
                    ->default(function () {
                        $loan = $this->getOwnerRecord();
                        $paymentSchedule = $loan->payment_schedule ?? [];
                        // get the first month in the payment schedule where the status is pending
                        $month = collect($paymentSchedule)->firstWhere('status', PaymentStatus::PENDING);

                        return $month ? $month['due_date'] : null;
                    }),
                Hidden::make('payment_date')
                    ->label(__('Payment Date'))
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
                    ->hidden(function (TextColumn $column) {

                        $state = $column->getState();

                        return $state !== PaymentMethod::BANK_TRANSFER || $state !== PaymentMethod::CHEQUE || $state !== PaymentMethod::MOBILE_MONEY || $state == null;
                    }),
                Tables\Columns\TextColumn::make('payment_reference')
                    ->hidden(function (TextColumn $column) {
                        $state = $column->getState();

                        return $state == null;
                    })
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

                        // Check total payments against loan amount
                        $totalPaid = $loan->payments()
                            ->where('status', PaymentStatus::COMPLETED)
                            ->sum('amount');

                        if (($totalPaid + $data['amount']) > $loan->amount) {
                            Notification::make()
                                ->warning()
                                ->title('Payment exceeds loan amount')
                                ->send();
                            $action->halt();
                        }

                        // Find scheduled payment for month
                        $scheduledPayment = collect($paymentSchedule)
                            ->firstWhere('month', $data['month']);

                        if (! $scheduledPayment) {
                            Notification::make()
                                ->warning()
                                ->title('Invalid payment month')
                                ->send();
                            $action->halt();
                        }

                        // Get total paid for this month
                        $monthlyPaid = $loan->payments()
                            ->where('month', $data['month'])
                            ->where('status', PaymentStatus::COMPLETED)
                            ->sum('amount');

                        // Update schedule status
                        $updatedSchedule = collect($paymentSchedule)
                            ->map(function ($payment) use ($data, $monthlyPaid) {
                                if ($payment['month'] === $data['month']) {
                                    $totalPaidForMonth = $monthlyPaid + $data['amount'];

                                    return array_merge($payment, [
                                        'status' => $totalPaidForMonth >= $payment['amount']
                                            ? PaymentStatus::COMPLETED->value
                                            : PaymentStatus::PENDING->value,
                                        'paid_amount' => $totalPaidForMonth,
                                    ]);
                                }

                                return $payment;
                            })
                            ->toArray();

                        $loan->forceFill(['payment_schedule' => $updatedSchedule])->saveQuietly();
                    }),
                // Tables\Actions\AssociateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->before(function (Tables\Actions\EditAction $action, array $data, Model $record) {
                        $loan = $this->getOwnerRecord();
                        $paymentSchedule = $loan->payment_schedule ?? [];

                        // Calculate total paid excluding current payment
                        $totalPaid = $loan->payments()
                            ->where('status', PaymentStatus::COMPLETED)
                            ->where('id', '!=', $record->id)
                            ->sum('amount');

                        // Check if new amount would exceed loan amount
                        if (($totalPaid + $data['amount']) > $loan->amount) {
                            Notification::make()
                                ->warning()
                                ->title('Payment exceeds loan amount')
                                ->send();
                            $action->halt();
                        }

                        // Get total paid for this month excluding current payment
                        $monthlyPaid = $loan->payments()
                            ->where('month', $data['month'])
                            ->where('status', PaymentStatus::COMPLETED)
                            ->where('id', '!=', $record->id)
                            ->sum('amount');

                        // Update schedule status
                        $updatedSchedule = collect($paymentSchedule)
                            ->map(function ($payment) use ($data, $monthlyPaid) {
                                if ($payment['month'] === $data['month']) {
                                    $totalPaidForMonth = $monthlyPaid + $data['amount'];

                                    return array_merge($payment, [
                                        'status' => $totalPaidForMonth >= $payment['amount']
                                            ? PaymentStatus::COMPLETED->value
                                            : PaymentStatus::PENDING->value,
                                        'paid_amount' => $totalPaidForMonth,
                                    ]);
                                }

                                return $payment;
                            })
                            ->toArray();

                        $loan->forceFill(['payment_schedule' => $updatedSchedule])->saveQuietly();
                    }),
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
