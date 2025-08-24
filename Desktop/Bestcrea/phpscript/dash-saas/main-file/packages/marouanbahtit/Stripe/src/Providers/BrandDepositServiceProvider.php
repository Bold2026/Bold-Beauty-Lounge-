<?php

namespace Workdo\Stripe\Providers;

use App\Models\WorkSpace;
use Illuminate\Support\ServiceProvider;

class BrandDepositServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function boot()
    {
        view()->composer(['influencer-marketing::frontend.brand.deposit'], function ($view) {
            $slug             = \Request::segment(1);
            $workspace        = WorkSpace::where('slug', $slug)->first();
            $company_settings = getCompanyAllSetting($workspace->created_by, $workspace->id);
            if (module_is_active('Stripe', $workspace->created_by) && (isset($company_settings['stripe_is_on']) ? $company_settings['stripe_is_on'] : 'off') == 'on' && !empty($company_settings['stripe_key']) && !empty($company_settings['stripe_secret'])) {
                $view->getFactory()->startPush('brand_deposit_payment', view('stripe::payment.brand_deposit_payment', compact('slug')));
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
