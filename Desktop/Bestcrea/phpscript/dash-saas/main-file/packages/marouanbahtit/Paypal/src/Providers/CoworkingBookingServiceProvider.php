<?php

namespace Workdo\Paypal\Providers;

use App\Models\WorkSpace;
use Illuminate\Support\ServiceProvider;

class CoworkingBookingServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */

    public function boot(){

        view()->composer(['coworking-space-management::frontend.booking_payment'], function ($view)
        {
            $slug = \Request::segment(2);
            $workspace = WorkSpace::where('slug', $slug)->first();
            $company_settings = getCompanyAllSetting($workspace->created_by,$workspace->id);
            $type = "booking";
            if((module_is_active('Paypal', $workspace->created_by) && isset($company_settings['paypal_payment_is_on']) ? $company_settings['paypal_payment_is_on'] : 'off') == 'on' && !empty($company_settings['company_paypal_client_id']) && !empty($company_settings['company_paypal_secret_key']))
            {
                $view->getFactory()->startPush('coworking_booking_payment', view('paypal::payment.coworking_payment',compact('type','slug')));
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
