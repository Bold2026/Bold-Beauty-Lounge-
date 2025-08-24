<?php

namespace Workdo\Paypal\Listeners;
use App\Events\SuperAdminSettingMenuEvent;

class SuperAdminSettingMenuListener
{
    /**
     * Handle the event.
     */
    public function handle(SuperAdminSettingMenuEvent $event): void
    {
        $module = 'Paypal';
        $menu = $event->menu;
        $menu->add([
            'title' => __('Paypal'),
            'name' => 'paypal',
            'order' => 1020,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => '',
            'navigation' => 'paypal-sidenav',
            'module' => $module,
            'permission' => 'paypal manage'
        ]);
    }
}
