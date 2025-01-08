<?php

namespace App\Observers;

use App\Enums\LoanStatus;
use App\Models\Loan;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class LoanObserver implements ShouldHandleEventsAfterCommit
{
    private function shouldGenerateSchedule(LoanStatus $status): bool
    {
        return in_array($status, [
            LoanStatus::APPROVED,

        ]);
    }

    /**
     * Handle the Loan "created" event.
     */
    public function created(Loan $loan): void
    {
        if ($this->shouldGenerateSchedule($loan->status)) {
            $updates = [];
            if (empty($loan->approved_at)) {
                $updates['approved_at'] = now();
            }

            // If we have updates, do them without triggering observers
            if (! empty($updates)) {
                $loan->timestamps = false; // Prevent updating timestamps
                $loan->unsetEventDispatcher(); // Prevent triggering observers again
                $loan->update($updates);
            }

            if (empty($loan->payment_schedule)) {
                $loan->generatePaymentSchedule();
            }

        }
        // if (
        //     $this->shouldGenerateSchedule($loan->status)
        //     && ! $this->isGeneratingSchedule
        //     && empty($loan->payment_schedule)
        // ) {
        //     $this->isGeneratingSchedule = true;
        //     $loan->generatePaymentSchedule(); // saveQuietly() inside
        //     $this->isGeneratingSchedule = false;
        // }
    }

    /**
     * Handle the Loan "updated" event.
     */
    public function updated(Loan $loan): void
    {
        if ($loan->isDirty('status') && $this->shouldGenerateSchedule($loan->status)) {
            $updates = [];

            if (empty($loan->approved_at)) {
                $updates['approved_at'] = now();
            }

            // If we have updates, do them without triggering observers
            if (! empty($updates)) {
                $loan->timestamps = false; // Prevent updating timestamps
                $loan->unsetEventDispatcher(); // Prevent triggering observers again
                $loan->update($updates);
            }

            if (empty($loan->payment_schedule)) {
                $loan->generatePaymentSchedule();
            }

        }

        // if ($loan->wasChanged('status')
        // && $this->shouldGenerateSchedule($loan->status)
        // && ! $this->isGeneratingSchedule
        // && empty($loan->payment_schedule)) {
        //     $this->isGeneratingSchedule = true;
        //     $loan->generatePaymentSchedule(); // saveQuietly() inside
        //     $this->isGeneratingSchedule = false;
        // }
    }

    /**
     * Handle the Loan "deleted" event.
     */
    public function deleted(Loan $loan): void
    {
        //
    }

    /**
     * Handle the Loan "restored" event.
     */
    public function restored(Loan $loan): void
    {
        //
    }

    /**
     * Handle the Loan "force deleted" event.
     */
    public function forceDeleted(Loan $loan): void
    {
        //
    }
}
