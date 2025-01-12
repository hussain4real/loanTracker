<?php

namespace App\Filament\Widgets;

use App\Enums\PaymentStatus;
use App\Models\User;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;

class Loanee extends ChartWidget
{
    protected static ?string $heading = 'Chart';

    protected static ?string $pollingInterval = '300s';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 2;

    protected static ?string $maxHeight = '750px';

    public ?string $filter = 'all';

    public function getHeading(): string|Htmlable|null
    {
        return __('Borrowers Overview');
    }

    public function getDescription(): string|Htmlable|null
    {
        return __('Total loans and payments made by each borrower.');
    }

    protected function getFilters(): ?array
    {
        $users = User::whereHas('loans')->get(['id', 'name']);

        $filters = ['all' => 'All Borrowers'];

        foreach ($users as $user) {
            $filters[$user->id] = $user->name;
        }

        return $filters;
    }

    protected function getData(): array
    {
        $query = User::query()
            ->with(['loans', 'payments' => function ($query) {
                $query->where('payments.status', PaymentStatus::COMPLETED);
            }]);

        if ($this->filter !== 'all') {
            $query->where('id', $this->filter);
        }

        $users = $query->get();

        $labels = [];
        $loanAmounts = [];
        $paidAmounts = [];
        $backgroundColors = [];

        foreach ($users as $user) {
            $labels[] = $user->name;

            $totalLoan = $user->loans->sum('amount');
            $loanAmounts[] = $totalLoan;

            $paid = $user->payments->sum('amount');
            $paidAmounts[] = $paid;

            if ($paid > 0) {
                // yellow
                $backgroundColors[] = 'rgba(255, 206, 86, 0.8)';
            } elseif ($paid == $totalLoan) {
                // green
                $backgroundColors[] = 'rgba(76, 175, 80, 0.8)';
            } else {
                // Orange
                $backgroundColors[] = 'rgba(255, 165, 80, 0.8)';
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Loans',
                    'data' => $loanAmounts,
                    'backgroundColor' => $backgroundColors,
                    'borderColor' => $backgroundColors,
                    'borderWidth' => 2,
                    'userNames' => $labels,
                ],
                [
                    'label' => 'Total Paid',
                    'data' => $paidAmounts,
                    'backgroundColor' => ['rgba(76, 175, 80, 0.8)'],
                    'borderColor' => ['rgba(76, 175, 80, 1)'],
                    'borderWidth' => 2,
                    'userNames' => $labels,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): RawJs
    {
        return RawJs::make(<<<'JS'
        {
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const userName = context.dataset.userNames[context.dataIndex];
                        const amount = new Intl.NumberFormat('en-US', {
                            style: 'currency',
                            currency: 'USD'
                        }).format(context.parsed);
                        
                        return `${userName} - ${context.dataset.label}: ${amount}`;
                        }
                    }
                },
                legend: {
                    position: 'bottom'
                }
            },
            scales: {
            x: {
                display: false
            },
            y: {
                display: false
            }
        },
            responsive: true
        }
        JS);
    }
}
