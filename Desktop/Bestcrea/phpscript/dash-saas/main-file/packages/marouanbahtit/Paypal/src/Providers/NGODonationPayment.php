<?php

namespace Workdo\Paypal\Providers;

use App\Models\WorkSpace;
use Illuminate\Support\ServiceProvider;

class NGODonationPayment extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function boot()
    {
        view()->composer(['ngomanagment::front_end.pages.donate-now', 'ngomanagment::front_end.index'], function ($view) {
            $slug             = \Request::segment(1);
            $workspace        = WorkSpace::where('slug', $slug)->first();
            $company_settings = getCompanyAllSetting($workspace->created_by, $workspace->id);
            if((module_is_active('Paypal', $workspace->created_by) && isset($company_settings['paypal_payment_is_on']) ? $company_settings['paypal_payment_is_on'] : 'off') == 'on' && !empty($company_settings['company_paypal_client_id']) && !empty($company_settings['company_paypal_secret_key']))
            {
                $view->getFactory()->startPush('ngo_donation_payment', view('paypal::payment.ngo_donation_payment', compact('slug')));
            }
        });
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