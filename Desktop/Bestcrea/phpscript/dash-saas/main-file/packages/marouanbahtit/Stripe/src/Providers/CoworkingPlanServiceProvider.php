<?php

namespace Workdo\Stripe\Providers;

use App\Models\WorkSpace;
use Illuminate\Support\ServiceProvider;

class CoworkingPlanServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */

    public function boot(){

        view()->composer(['coworking-space-management::frontend.plan_login'], function ($view)
        {
            $slug = \Request::segment(2);
            $workspace = WorkSpace::where('slug',$slug)->first();
            $company_settings = getCompanyAllSetting($workspace->created_by,$workspace->id);
            $type = "membership";
            if(module_is_active('Stripe', $workspace->created_by) && (isset($company_settings['stripe_is_on']) ? $company_settings['stripe_is_on'] : 'off') == 'on' && !empty($company_settings['stripe_key']) && !empty($company_settings['stripe_secret']))
            {
                $view->getFactory()->startPush('coworking_mebership_payment', view('stripe::payment.coworking_payment',compact('type','slug')));
            }
        });
    }

    public function register()
    {

    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}
