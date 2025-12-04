<?php

namespace App\Providers;

use App\Models\Invoice;
use App\Models\Vendor;
use App\Observers\InvoiceObserver;
use App\Observers\VendorObserver;
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
        // Register model observers for stats cache invalidation
        Invoice::observe(InvoiceObserver::class);
        Vendor::observe(VendorObserver::class);
    }
}
