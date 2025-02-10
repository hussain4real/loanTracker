<?php

namespace App\Observers;

use App\Enums\LoanStatus;
use App\Models\Loan;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class LoanObserver implements ShouldHandleEventsAfterCommit
{
    /**
     * Handle the Loan "created" event.
     */
    public function created(Loan $loan): void
    {
        $loan->generatePaymentSchedule();
    }

    /**
     * Handle the Loan "updated" event.
     */
    public function updated(Loan $loan): void
    {
        $loan->generatePaymentSchedule();
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
