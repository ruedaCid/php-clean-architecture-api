<?php

namespace App\Providers;

use App\Domain\Customer\CustomerRepository;
use App\Infrastructure\Persistence\Eloquent\EloquentCustomerRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            CustomerRepository::class,
            EloquentCustomerRepository::class
        );
    }

    public function boot(): void
    {
        //
    }
}
