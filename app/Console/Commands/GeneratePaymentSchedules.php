<?php

namespace App\Console\Commands;

use App\Models\Loan;
use Illuminate\Console\Command;

class GeneratePaymentSchedules extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'loan:generate-payment-schedules';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate payment schedules for all existing loans';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Generating payment schedules...');
        Loan::chunk(100, function ($loans) {
            $loans->each->generatePaymentSchedule();
            $this->info('Generated payment schedules for ' . $loans->count() . ' loans with IDs: ' . $loans->pluck('id')->join(', '));
        });

        $this->info('Payment schedules generated successfully!');
    }
}
