<?php

namespace Workdo\Paypal\Providers;

use App\Models\WorkSpace;
use Illuminate\Support\ServiceProvider;

class ArtShowcasePaymentSerivceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */

    public function boot(){

        view()->composer(['art-showcase::frontend.layouts.footer'], function ($view)
        {
            $slug = \Request::segment(1);
            $workspace = WorkSpace::where('slug',$slug)->first();
            $company_settings = getCompanyAllSetting($workspace->created_by,$workspace->id);
            if((isset($company_settings['paypal_payment_is_on']) ? $company_settings['paypal_payment_is_on'] : 'off') == 'on' && !empty($company_settings['company_paypal_client_id']) && !empty($company_settings['company_paypal_secret_key']))
            {
                $view->getFactory()->startPush('art_showcase_payment', view('paypal::payment.art_showcase_payment',compact('slug')));
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