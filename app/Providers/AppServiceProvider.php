<?php

namespace App\Providers;

use App\Models\Loan;
use App\Models\Payment;
use BezhanSalleh\FilamentLanguageSwitch\LanguageSwitch;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
            $switch->locales([
                'en',
                'ar',
            ])
                ->visible(outsidePanels: true);
        });
        Loan::observe(\App\Observers\LoanObserver::class);
        Payment::observe(\App\Observers\PaymentObserver::class);
    }
}
