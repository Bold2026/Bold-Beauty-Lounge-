<?php

namespace Workdo\LandingPage\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as Provider;
use Workdo\LandingPage\Listeners\SuperAdminMenuListener;
use App\Events\SuperAdminMenuEvent;

class EventServiceProvider extends Provider
{
    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    protected $listen = [
        SuperAdminMenuEvent::class =>[
            SuperAdminMenuListener::class
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
