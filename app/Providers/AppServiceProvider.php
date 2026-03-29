<?php

declare(strict_types=1);

namespace App\Providers;

use App\Application\Policies\ChangeOrderPolicy;
use App\Domain\ChangeOrder\Models\ChangeOrder;
use Illuminate\Support\Facades\Gate;
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
        Gate::policy(ChangeOrder::class, ChangeOrderPolicy::class);
    }
}
