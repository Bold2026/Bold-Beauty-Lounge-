<?php

namespace Workdo\Lead\Providers;

use App\Events\CompanyMenuEvent;
use App\Events\DefaultData;
use App\Events\GivePermissionToRole;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as Provider;
use Workdo\Lead\Listeners\CompanyMenuListener;
use Workdo\Lead\Listeners\DataDefault;
use Workdo\Lead\Listeners\GiveRoleToPermission;

class EventServiceProvider extends Provider
{
    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return true;
    }

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
    protected $listen = [
        CompanyMenuEvent::class => [
            CompanyMenuListener::class
        ],
        DefaultData::class => [
            DataDefault::class
        ],
        GivePermissionToRole::class => [
            GiveRoleToPermission::class
        ],
    ];
}
