<?php

namespace App\Observers;

use App\Models\Payment;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PaymentObserver implements ShouldQueue, ShouldHandleEventsAfterCommit
{
    use InteractsWithQueue;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 120;

    private function debouncedGenerateSchedule(Payment $payment): void
    {
        $loanId = $payment->loan_id;
        $cacheKey = "generating_schedule_{$loanId}";

        if (!Cache::has($cacheKey)) {
            Cache::put($cacheKey, true, now()->addSeconds(5));
            try {
                Log::info('Generating payment schedule for loan', [
                    'loan_id' => $loanId,
                    'payment_id' => $payment->id,
                    'sequence_number' => $payment->month,
                    'amount' => $payment->amount
                ]);
                $payment->loan->generatePaymentSchedule();
            } catch (\Exception $e) {
                Log::error('Error generating payment schedule: ' . $e->getMessage());
                $this->fail($e);
            } finally {
                Cache::forget($cacheKey);
            }
        } else {
            Log::info('Skipping duplicate schedule generation', [
                'loan_id' => $loanId,
                'sequence_number' => $payment->month
            ]);
        }
    }

    /**
     * Handle the Payment "created" event.
     */
    public function created(Payment $payment): void
    {
        try {
            Log::info('Payment created observer triggered', [
                'payment_id' => $payment->id,
                'amount' => $payment->amount,
                'sequence_number' => $payment->month,
                'status' => $payment->status,
            ]);
            $this->debouncedGenerateSchedule($payment);
        } catch (\Exception $e) {
            Log::error('Error in payment observer: ' . $e->getMessage());
            $this->fail($e);
        }
    }

    /**
     * Handle the Payment "updated" event.
     */
    public function updated(Payment $payment): void
    {
        try {
            Log::info('Payment updated observer triggered', [
                'payment_id' => $payment->id,
                'amount' => $payment->amount,
                'sequence_number' => $payment->month,
                'status' => $payment->status,
            ]);
            $this->debouncedGenerateSchedule($payment);
        } catch (\Exception $e) {
            Log::error('Error in payment observer: ' . $e->getMessage());
            $this->fail($e);
        }
    }

    /**
     * Handle the Payment "deleted" event.
     */
    public function deleted(Payment $payment): void
    {
        try {
            Log::info('Payment deleted observer triggered', [
                'payment_id' => $payment->id,
                'sequence_number' => $payment->month,
            ]);
            $this->debouncedGenerateSchedule($payment);
        } catch (\Exception $e) {
            Log::error('Error in payment observer: ' . $e->getMessage());
            $this->fail($e);
        }
    }

    /**
     * Handle the Payment "restored" event.
     */
    public function restored(Payment $payment): void
    {
        //
    }

    /**
     * Handle the Payment "force deleted" event.
     */
    public function forceDeleted(Payment $payment): void
    {
        //
    }
}
