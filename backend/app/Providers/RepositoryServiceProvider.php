<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\Repositories\InvoiceRepositoryInterface;
use App\Contracts\Repositories\VendorRepositoryInterface;
use App\Repositories\InvoiceRepository;
use App\Repositories\VendorRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * The repository bindings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected array $repositories = [
        VendorRepositoryInterface::class => VendorRepository::class,
        InvoiceRepositoryInterface::class => InvoiceRepository::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        foreach ($this->repositories as $interface => $implementation) {
            $this->app->bind($interface, $implementation);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
