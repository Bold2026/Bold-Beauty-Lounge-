<?php

namespace Workdo\Hrm\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as Provider;
use App\Events\CompanyMenuEvent;
use App\Events\CompanySettingEvent;
use App\Events\CompanySettingMenuEvent;
use App\Events\CreateUser;
use App\Events\DefaultData;
use App\Events\GivePermissionToRole;
use App\Events\UpdateUser;
use Workdo\Assets\Events\CreateAssets;
use Workdo\Assets\Events\UpdateAssets;
use Workdo\Hrm\Listeners\CompanyMenuListener;
use Workdo\Hrm\Listeners\CompanySettingListener;
use Workdo\Hrm\Listeners\CompanySettingMenuListener;
use Workdo\Hrm\Listeners\CreateAssetsLis;
use Workdo\Hrm\Listeners\DataDefault;
use Workdo\Hrm\Listeners\GiveRoleToPermission;
use Workdo\Hrm\Listeners\UpdateAssetsLis;
use Workdo\Hrm\Listeners\UserCreate;
use Workdo\Hrm\Listeners\UserUpdate;

class EventServiceProvider extends Provider
{
    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    protected $listen = [
        CompanyMenuEvent::class => [
            CompanyMenuListener::class,
        ],
        CompanySettingEvent::class => [
            CompanySettingListener::class,
        ],
        CompanySettingMenuEvent::class => [
            CompanySettingMenuListener::class,
        ],
        CreateAssets::class => [
            CreateAssetsLis::class,
        ],
        UpdateAssets::class => [
            UpdateAssetsLis::class,
        ],
        CreateUser::class => [
            UserCreate::class
        ],
        UpdateUser::class => [
            UserUpdate::class
        ],
        DefaultData::class => [
            DataDefault::class
        ],
        GivePermissionToRole::class => [
            GiveRoleToPermission::class
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
