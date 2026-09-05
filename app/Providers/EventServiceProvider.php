<?php

namespace App\Providers;

use App\Events\NewEmail;
use App\Events\NewOrder;
use App\Events\OrderChangeStatus;
use App\Events\ClientViewCategory;
use App\Events\OrderStatusChanged;
use App\Listeners\NewEmailListener;
use Illuminate\Support\Facades\Event;
use App\Listeners\OrderChangeListener;
use Illuminate\Auth\Events\Registered;
use App\Listeners\NewOrderCreatedListener;
use App\Listeners\ClientViewCategoryListener;
use App\Listeners\InformClientOrderChange;
use App\Listeners\InformOrderClient;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        NewEmail::class => [
            NewEmailListener::class,
        ],
        ClientViewCategory::class => [
            ClientViewCategoryListener::class
        ],
        OrderChangeStatus::class => [
            OrderChangeListener::class,
        ],
        NewOrder::class=>[
            NewOrderCreatedListener::class,
            InformOrderClient::class,
        ],
        OrderStatusChanged::class => [
            InformClientOrderChange::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
