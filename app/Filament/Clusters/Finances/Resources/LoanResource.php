<?php

namespace App\Filament\Clusters\Finances\Resources;

use App\Enums\LoanStatus;
use App\Enums\Purpose;
use App\Filament\Clusters\Finances;
use App\Filament\Clusters\Finances\Resources\LoanResource\Pages;
use App\Filament\Clusters\Finances\Resources\LoanResource\RelationManagers\PaymentsRelationManager;
use App\Filament\Clusters\Finances\Resources\LoanResource\Widgets\LoanOverview;
use App\Models\Loan;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\Layout\View;
use Filament\Tables\Table;

class LoanResource extends Resource
{
    protected static ?string $model = Loan::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-pointing-out';

    protected static ?string $cluster = Finances::class;

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function getPluralModelLabel(): string
    {
        $userName = auth()->user()->name;

        return __("Money Loaned by {$userName} to Others");
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label(__('Borrower/Client'))
                    ->relationship(name: 'user', titleAttribute: 'name', modifyQueryUsing: fn ($query) => $query->where('id', '!=', auth()->id()))

                    ->createOptionForm([
                        Grid::make()
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->required(),
                                Forms\Components\Hidden::make('email')
                                    ->default(function ($state, Get $get) {

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
                                Forms\Components\TextInput::make('city')
                                    ->label('City'),
                                Forms\Components\TextInput::make('state')
                                    ->label('State'),
                                Forms\Components\TextInput::make('country')
                                    ->label('Country'),

                            ]),
                    ])
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('amount')
                    ->numeric(),
                Forms\Components\Select::make('purpose')
                    ->options(Purpose::class),
                Forms\Components\Select::make('status')
                    ->options(LoanStatus::class)
                    ->live(onBlur: true),
                // Forms\Components\DatePicker::make('approved_at')
                //     ->visible(fn (Get $get) => $get('status') === LoanStatus::APPROVED->value || $get('status') === LoanStatus::ACTIVE->value),
                Forms\Components\TextInput::make('duration')
                    ->numeric()
                    ->suffix('months')
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Set $set, $state, Get $get) {
                        logger('After state updated triggered.', [$state]);
                        $duration = (int) $state; // Ensure the value is cast to an integer
                        logger('Duration', [$duration]);
                        $dueDate = now()->addMonths($duration)->startOfDay()->format('Y-m-d');
                        logger('Due Date', [$dueDate]);
                        if ($duration > 0) {
                            $set('due_date', $dueDate);
                        } else {
                            $set('due_date', null); // Handle invalid or zero duration
                        }
                    }),
                Forms\Components\DatePicker::make('due_date'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Stack::make([
                    View::make('filament.tables.loan')
                        ->components([
                            Tables\Columns\TextColumn::make('user.name')
                                ->searchable()
                                ->sortable(),
                            Tables\Columns\TextColumn::make('amount')
                                ->numeric()
                                ->sortable(),
                            Tables\Columns\TextColumn::make('purpose')
                                ->searchable(),
                            Tables\Columns\TextColumn::make('status')
                                ->searchable()
                                ->sortable(),
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
                        ]),
                ])
                    ->extraAttributes(['id' => 'hello']),
            ])

            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ])
            ->persistSearchInSession()
            ->persistColumnSearchesInSession()
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\ViewAction::make(),
                // Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            PaymentsRelationManager::class,
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

    public static function getWidgets(): array
    {
        return [
            LoanOverview::class,
        ];
    }
}
