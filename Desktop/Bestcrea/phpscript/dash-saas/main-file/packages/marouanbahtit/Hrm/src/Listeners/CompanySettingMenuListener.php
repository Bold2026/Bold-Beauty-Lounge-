<?php

namespace Workdo\Hrm\Listeners;

use App\Events\CompanySettingMenuEvent;

class CompanySettingMenuListener
{
    /**
     * Handle the event.
     */
    public function handle(CompanySettingMenuEvent $event): void
    {
        $module = 'Hrm';
        $menu = $event->menu;
        $menu->add([
            'title' => __('Hrm Settings'),
            'name' => 'hrm-setting',
            'order' => 130,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => '',
            'navigation' => 'hrm-sidenav',
            'module' => $module,
            'permission' => 'hrm manage'
        ]);
    }
}
