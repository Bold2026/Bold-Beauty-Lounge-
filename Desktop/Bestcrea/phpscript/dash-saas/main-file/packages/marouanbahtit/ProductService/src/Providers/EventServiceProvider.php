<?php

namespace Workdo\ProductService\Providers;  

use App\Events\CompanyMenuEvent;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as Provider;
use Workdo\ProductService\Listeners\CompanyMenuListener;

class EventServiceProvider extends Provider
{
    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */

    protected $listen = [
        CompanyMenuEvent::class => [
            CompanyMenuListener::class
        ]
    ];

    /**
     * Get the listener directories that should be used to discover events.
     *
     * @return array
     */
    protected function discoverEventsWithin()
    {
        return [
            __DIR__ . '/../Listeners',
        ];
    }


}
