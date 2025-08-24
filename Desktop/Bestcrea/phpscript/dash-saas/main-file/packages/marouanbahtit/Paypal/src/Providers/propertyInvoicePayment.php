<?php

namespace Workdo\Paypal\Providers;

use App\Models\WorkSpace;
use Illuminate\Support\ServiceProvider;

class propertyInvoicePayment extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */


    public function boot(){
        view()->composer(['property-management::propertyinvoice.view'], function ($view)
        {
            if(\Auth::check())
            {
                $company_settings = getCompanyAllSetting();
                if(module_is_active('Paypal', \Auth::user()->created_by) && isset($company_settings['paypal_payment_is_on']) && $company_settings['paypal_payment_is_on'] == 'on' && !empty($company_settings['company_paypal_client_id']) && !empty($company_settings['company_paypal_secret_key']))
                {
                    $view->getFactory()->startPush('company_property_payment', view('paypal::payment.property_payment'));
                }
            }

        });

        view()->composer(['property-management::frontend.checkout'], function ($view)
        {
                $slug = \Request::segment(2);

                $workspace = WorkSpace::where('slug',$slug)->first();

                $company_settings = getCompanyAllSetting($workspace->created_by,$workspace->id);

                if((isset($company_settings['paypal_payment_is_on']) ? $company_settings['paypal_payment_is_on'] : 'off') == 'on' && !empty($company_settings['company_paypal_client_id']) && !empty($company_settings['company_paypal_secret_key']))
                {
                    $view->getFactory()->startPush('property_payment', view('paypal::payment.property_booking_payment',compact('slug')));
                }
        });

    }
    public function register()
    {
        //
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
