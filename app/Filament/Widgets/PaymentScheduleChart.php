<?php

namespace App\Filament\Widgets;

use App\Models\Loan;
use App\Models\User;
use Filament\Widgets\ChartWidget;

class PaymentScheduleChart extends ChartWidget
{
    protected static ?string $heading = 'Payment Schedule';

    protected static ?string $pollingInterval = '300s';

    protected int|string|array $columnSpan = 'full';

    public ?string $filter = 'all';

    protected function getFilters(): ?array
    {
        // Get all users that have loans
        $users = User::whereHas('loans')->get();

        // Start with an 'all' option
        $filters = ['all' => 'All Users'];

        // Add each user as a filter option
        foreach ($users as $user) {
            $filters[$user->id] = $user->name; // Assuming your User model has a 'name' field
        }

        return $filters;
    }

    protected function getData(): array
    {
        $query = Loan::query()->with('user');

        // Apply user filter if not 'all'
        if ($this->filter !== 'all') {
            $query->where('user_id', $this->filter);
        }
        $loans = $query->select('id', 'payment_schedule', 'user_id')->get();

        // Initialize arrays to store aggregated data
        $monthlyTotals = [];
        $paidAmounts = [];

        foreach ($loans as $loan) {
            $schedule = $loan->payment_schedule;

            if (! is_array($schedule)) {
                continue;
            }

            foreach ($schedule as $payment) {
                $month = $payment['month'];
                $amount = $payment['amount'];
                $paidAmount = $payment['paid_amount'] ?? 0;

                // Aggregate amounts for each month
                if (! isset($monthlyTotals[$month])) {
                    $monthlyTotals[$month] = 0;
                    $paidAmounts[$month] = 0;
                }

                $monthlyTotals[$month] += $amount;
                $paidAmounts[$month] += $paidAmount;
            }
        }

        // Sort months chronologically
        $months = array_keys($monthlyTotals);
        $monthOrder = [
            'January',
            'February',
            'March',
            'April',
            'May',
            'June',
            'July',
            'August',
            'September',
            'October',
            'November',
            'December',
        ];
        usort($months, function ($a, $b) use ($monthOrder) {
            return array_search($a, $monthOrder) - array_search($b, $monthOrder);
        });

        // Prepare datasets
        $scheduledAmounts = array_map(function ($month) use ($monthlyTotals) {
            return $monthlyTotals[$month];
        }, $months);

        $paidAmountsData = array_map(function ($month) use ($paidAmounts) {
            return $paidAmounts[$month];
        }, $months);

        return [
            'datasets' => [
                [
                    'label' => 'Scheduled Amount',
                    'data' => $scheduledAmounts,
                    'backgroundColor' => 'rgba(255, 206, 86, 0.8)', // Yellow with transparency
                    'borderColor' => 'rgba(255, 206, 86, 1)', // Yellow border
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'Paid Amount',
                    'data' => $paidAmountsData,
                    'backgroundColor' => 'rgba(76, 175, 80, 0.8)', // Green with transparency
                    'borderColor' => 'rgba(76, 175, 80, 1)', // Green border
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $months,

        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    //
    // protected function getOptions(): array
    // {
    //     return [
    //         'scales' => [
    //             'y' => [
    //                 'ticks' => [
    //                     'callback' => "function(value) {
    //                         return '$' + value.toLocaleString();
    //                     }",
    //                 ],
    //             ],
    //         ],
    //         'plugins' => [
    //             'tooltip' => [
    //                 'callbacks' => [
    //                     'label' => "function(context) {
    //                         return context.dataset.label + ': $' + context.parsed.y.toLocaleString();
    //                     }",
    //                 ],
    //             ],
    //         ],
    //     ];
    // }
}
