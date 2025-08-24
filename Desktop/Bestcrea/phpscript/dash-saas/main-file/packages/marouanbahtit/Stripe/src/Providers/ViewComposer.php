<?php

namespace Workdo\Stripe\Providers;

use App\Facades\ModuleFacade as Module;
use Illuminate\Support\ServiceProvider;

class ViewComposer extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function boot(){
        view()->composer(['plans.marketplace','plans.planpayment'], function ($view)
        {
            if(\Auth::check())
            {
                $admin_settings = getAdminAllSetting();
                if((Module::isEnabled('Stripe') && isset($admin_settings['stripe_is_on']) ? $admin_settings['stripe_is_on'] : 'off') == 'on' && !empty($admin_settings['stripe_key']) && !empty($admin_settings['stripe_secret']))
                {
                    $view->getFactory()->startPush('company_plan_payment', view('stripe::payment.plan_payment'));
                }
            }

        });
    }

}
