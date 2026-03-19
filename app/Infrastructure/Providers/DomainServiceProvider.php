<?php

declare(strict_types=1);

namespace App\Infrastructure\Providers;

use App\Domain\ChangeOrder\Contracts\ChangeOrderRepositoryInterface;
use App\Domain\ChangeOrder\Events\ChangeOrderApproved;
use App\Domain\ChangeOrder\Events\ChangeOrderRejected;
use App\Domain\ChangeOrder\Events\ChangeOrderSubmitted;
use App\Domain\ProjectBudget\Contracts\BudgetRepositoryInterface;
use App\Infrastructure\Listeners\BroadcastChangeOrderUpdate;
use App\Infrastructure\Listeners\LogChangeOrderStateTransition;
use App\Infrastructure\Listeners\UpdateBudgetOnChangeOrderApproval;
use App\Infrastructure\Repositories\EloquentBudgetRepository;
use App\Infrastructure\Repositories\EloquentChangeOrderRepository;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

final class DomainServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ChangeOrderRepositoryInterface::class, EloquentChangeOrderRepository::class);
        $this->app->bind(BudgetRepositoryInterface::class, EloquentBudgetRepository::class);
    }

    public function boot(): void
    {
        Event::listen(ChangeOrderSubmitted::class, LogChangeOrderStateTransition::class);
        Event::listen(ChangeOrderSubmitted::class, BroadcastChangeOrderUpdate::class);

        Event::listen(ChangeOrderApproved::class, UpdateBudgetOnChangeOrderApproval::class);
        Event::listen(ChangeOrderApproved::class, LogChangeOrderStateTransition::class);
        Event::listen(ChangeOrderApproved::class, BroadcastChangeOrderUpdate::class);

        Event::listen(ChangeOrderRejected::class, LogChangeOrderStateTransition::class);
        Event::listen(ChangeOrderRejected::class, BroadcastChangeOrderUpdate::class);
    }
}
