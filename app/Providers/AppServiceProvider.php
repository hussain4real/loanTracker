<?php

namespace App\Providers;

use BezhanSalleh\FilamentLanguageSwitch\LanguageSwitch;
use Illuminate\Support\ServiceProvider;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Queue;

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

        Queue::before(function (JobProcessing $event) {
            // Set a high timeout for payment schedule generation
            if (str_contains($event->job->payload()['displayName'] ?? '', 'PaymentObserver')) {
                $event->job->timeout = 120;
            }
        });
    }
}
