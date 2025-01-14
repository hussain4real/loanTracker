<?php

namespace App\Filament\Clusters\Finances\Resources\LoanResource\Widgets;

use App\Enums\PaymentStatus;
use App\Models\Loan;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Number;

class LoanOverview extends BaseWidget
{
    public ?Model $record = null;

    protected static ?string $pollingInterval = '300s';

    private float $totalLoans;

    private float $totalPaid;

    protected function getStats(): array
    {
        // Cache calculations
        $this->totalLoans = $this->getLoanQuery()->sum('amount');
        $this->totalPaid = $this->getLoanQuery()
            ->withSum([
                'payments' => fn ($query) => $query
                    ->where('status', PaymentStatus::COMPLETED),
            ], 'amount')
            ->get()
            ->sum('payments_sum_amount');

        return [
            Stat::make('Total Loans', "{$this->formatAmount($this->totalLoans)}")
                ->description('Total fees to be paid')
                ->descriptionIcon('heroicon-m-banknotes')
                ->chart($this->getChartData('total'))
                ->color('primary')
                ->extraAttributes([
                    'class' => 'shadow-md transition duration-700 ease-in-out transform hover:-translate-y-1 hover:scale-110',
                    'wire:ignore' => true,
                ]),
            Stat::make('Loans Paid', "{$this->formatAmount($this->totalPaid)} ")
                ->description('Total fees paid')
                ->descriptionIcon('heroicon-m-banknotes')
                ->chart($this->getChartData('paid'))
                ->color('success')
                ->extraAttributes([
                    'class' => 'shadow-md transition duration-700 ease-in-out transform hover:-translate-y-1 hover:scale-110',
                    'wire:ignore' => true,
                ]),
            Stat::make('Loans Unpaid', "{$this->formatAmount($this->totalLoans - $this->totalPaid)}")
                ->description('Total fees unpaid')
                ->descriptionIcon('heroicon-m-banknotes')
                ->chart($this->getChartData('unpaid'))
                ->color('warning')
                ->extraAttributes([
                    'class' => 'shadow-md transition duration-700 ease-in-out transform hover:-translate-y-1 hover:scale-110',
                    'wire:ignore' => true,
                ]),
        ];
    }

    private function getLoanQuery()
    {
        $query = Loan::when($this->record?->id, fn ($query, $id) => $query->where('id', $id));

        return $query;
    }

    // private function getTotalLoans(): float
    // {
    //     return round($this->getLoanQuery()->sum('amount'), 2);
    // }

    // private function getTotalLoansPaid(): float
    // {
    //     return round($this->getLoanQuery()
    //         ->withSum('payments', 'amount')
    //         ->get()
    //         ->sum('payments_sum_amount'), 2);
    // }

    // private function getTotalLoansToBePaid(): float
    // {
    //     return round($this->getLoanQuery()->sum('amount') - $this->getTotalLoansPaid(), 2);
    // }

    // private function getChartData(string $type): array
    // {
    //     $sixMonthsAgo = now()->subMonths(6)->startOfMonth();

    //     switch ($type) {
    //         case 'total':
    //             return $this->getLoanQuery()
    //                 ->where('created_at', '>=', $sixMonthsAgo)
    //                 ->orderBy('created_at')
    //                 ->get()
    //                 ->groupBy(fn ($loan) => $loan->created_at->format('M'))
    //                 ->map(fn ($loans) => $loans->sum('amount'))
    //                 ->values()
    //                 ->toArray();

    //         case 'paid':
    //             return $this->getLoanQuery()
    //                 ->with(['payments' => fn ($query) => $query
    //                     ->where('created_at', '>=', $sixMonthsAgo)
    //                     ->where('status', 'COMPLETED'),
    //                 ])
    //                 ->get()
    //                 ->pluck('payments')
    //                 ->flatten()
    //                 ->groupBy(fn ($payment) => $payment->created_at->format('M'))
    //                 ->map(fn ($payments) => $payments->sum('amount'))
    //                 ->values()
    //                 ->toArray();

    //         case 'unpaid':
    //             $totals = $this->getChartData('total');
    //             $paid = $this->getChartData('paid');

    //             return array_map(
    //                 fn ($total, $paid) => $total - ($paid ?? 0),
    //                 $totals,
    //                 array_pad($paid, count($totals), 0)
    //             );
    //     }
    // }

    // private function getChartData(string $type): array
    // {
    //     $data = match ($type) {
    //         'total' => $this->getLoanQuery()
    //             ->orderBy('created_at')
    //             ->get()
    //             ->groupBy(fn ($loan) => $loan->created_at->format('H:i'))
    //             ->map(fn ($loans) => $loans->sum('amount'))
    //             ->values()
    //             ->toArray(),

    //         'paid' => $this->getLoanQuery()
    //             ->with(['payments' => fn ($query) => $query
    //                 ->where('status', PaymentStatus::COMPLETED),
    //             ])
    //             ->get()
    //             ->pluck('payments')
    //             ->flatten()
    //             ->groupBy(fn ($payment) => $payment->created_at->format('H:i'))
    //             ->map(fn ($payments) => $payments->sum('amount'))
    //             ->values()
    //             ->toArray(),

    //         'unpaid' => array_map(
    //             fn ($total, $paid) => $total - ($paid ?? 0),
    //             $this->getChartData('total'),
    //             array_pad($this->getChartData('paid'), count($this->getChartData('total')), 0)
    //         ),
    //     };

    //     // Return sample data if empty
    //     if (empty($data)) {
    //         return [10, 20, 30, 40, 50, 60];
    //     }

    //     logger()->debug("Chart data for {$type}:", $data);

    //     return $data;
    // }
    private function formatAmount(float $amount): string
    {
        // return number_format($amount, 2);
        return Number::currency($amount, 'OMR');
    }

    private function getChartData(string $type): array
    {
        $amount = match ($type) {
            'total' => $this->totalLoans,
            'paid' => $this->totalPaid,
            'unpaid' => $this->totalLoans - $this->totalPaid,
        };

        $points = 6;
        $step = $amount / $points;

        return array_map(
            fn ($i) => round($step * ($i + 1), 2),
            range(0, $points - 1)
        );
    }
}
