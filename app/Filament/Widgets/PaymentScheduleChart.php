<?php

namespace App\Filament\Widgets;

use App\Models\Loan;
use App\Models\User;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\DB;

class PaymentScheduleChart extends ChartWidget
{
    use InteractsWithPageFilters;

    // protected static ?string $heading = 'Payment Schedule';

    protected static ?string $pollingInterval = '300s';

    protected int|string|array $columnSpan = 'full';

    protected static ?string $maxHeight = '700px';

    public ?string $filter = 'all';

    protected static ?int $sort = 1;

    public function getHeading(): string|Htmlable|null
    {
        return __('Loan Payment Schedule');
    }

    protected function getFilters(): ?array
    {
        // Get all users that have loans
        $users = User::whereHas('loans')->get(['id', 'name']);
        // Only select necessary fields and limit to users with loans
        // $users = DB::table('users')
        //     ->select('users.id', 'users.name')
        //     ->join('loans', 'users.id', '=', 'loans.user_id')
        //     ->distinct()
        //     ->get();

        // Start with an 'all' option
        $filters = ['all' => 'All Borrowers'];

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

        // Process loans in chunks to reduce memory usage
        // Loan::query()
        //     ->when($this->filter !== 'all', function ($query) {
        //         $query->where('user_id', $this->filter);
        //     })
        //     ->select('id', 'payment_schedule')
        //     ->chunkById(100, function ($loans) use (&$monthlyTotals, &$paidAmounts) {
        //         foreach ($loans as $loan) {
        //             $schedule = $loan->payment_schedule;

        //             if (! is_array($schedule)) {
        //                 continue;
        //             }

        //             foreach ($schedule as $payment) {
        //                 $month = $payment['month'];
        //                 $amount = $payment['amount'];
        //                 $paidAmount = $payment['paid_amount'] ?? 0;

        //                 if (! isset($monthlyTotals[$month])) {
        //                     $monthlyTotals[$month] = 0;
        //                     $paidAmounts[$month] = 0;
        //                 }

        //                 $monthlyTotals[$month] += $amount;
        //                 $paidAmounts[$month] += $paidAmount;
        //             }
        //         }
        //     });

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
                    'hoverBackgroundColor' => 'rgba(255, 206, 86, 1)', // Yellow on hover
                    'borderRadius' => 2,
                    // 'barPercentage' => 0.8,
                    // 'barThickness' => 40,
                    // 'maxBarThickness' => 50,

                ],
                [
                    'label' => 'Paid Amount',
                    'data' => $paidAmountsData,
                    'backgroundColor' => 'rgba(76, 175, 80, 0.8)', // Green with transparency
                    'borderColor' => 'rgba(76, 175, 80, 1)', // Green border
                    'borderWidth' => 2,
                    'hoverBackgroundColor' => 'rgba(76, 175, 80, 1)', // Green on hover
                    'borderRadius' => 2,

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
    protected function getOptions(): RawJs
    {
        // return [
        //     'indexAxis' => 'x',
        //     'scales' => [
        //         'x' => [
        //             'stacked' => false,
        //         ],
        //         'y' => [
        //             'stacked' => false,
        //             // i want to append $ to the y-axis
        //             'ticks' => [
        //                 'callback' => 'function(value) {
        //                     return "$" + value;
        //                 }',
        //             ],
        //         ],
        //     ],

        // ];
        return RawJs::make(<<<'JS'
            {
                indexAxis: 'x',
                scales: {
                    x: {
                        stacked: false,
                    },
                    y: {
                        stacked: false,
                        ticks: {
                            callback: function(value) {
                                return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(value);
                            },
                        },
                    },
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                var label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(context.parsed.y);
                                }
                                return label;
                            },
                        },
                    },
                    legend: {
                        position: 'bottom',
                    },
                    subtitle: {
                        display: false,
                        text: 'Toggle between scheduled and paid amounts',
                    },
                },
                responsive: true,
            }
            JS);
    }

    // Cache the chart data for 5 minutes to improve performance
    protected function getCacheLifetime(): ?string
    {
        return '5 minutes';
    }

    public function getDescription(): string|Htmlable|null
    {
        return __('This chart shows the scheduled and paid amounts for each month.');
    }
}
