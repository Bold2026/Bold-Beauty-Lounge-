<?php

namespace Workdo\Paypal\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as Provider;
use App\Events\CompanySettingEvent;
use App\Events\CompanySettingMenuEvent;
use App\Events\SuperAdminSettingEvent;
use App\Events\SuperAdminSettingMenuEvent;
use Workdo\Paypal\Listeners\CompanySettingListener;
use Workdo\Paypal\Listeners\CompanySettingMenuListener;
use Workdo\Paypal\Listeners\SuperAdminSettingListener;
use Workdo\Paypal\Listeners\SuperAdminSettingMenuListener;

class EventServiceProvider extends Provider
{
    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    protected $listen = [
        CompanySettingEvent::class => [
            CompanySettingListener::class,
        ],
        CompanySettingMenuEvent::class => [
            CompanySettingMenuListener::class,
        ],
        SuperAdminSettingEvent::class => [
            SuperAdminSettingListener::class,
        ],
        SuperAdminSettingMenuEvent::class => [
            SuperAdminSettingMenuListener::class,
        ],
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
