<?php

namespace Workdo\Paypal\Providers;

use App\Models\WorkSpace;
use Workdo\VCard\Entities\Business;
use Workdo\VCard\Entities\CardPayment;
use Illuminate\Support\ServiceProvider;

class VcardSerivceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */

    public function boot()
    {

        view()->composer(['vcard::business.edit'], function ($view) {
            $id = \Request::segment(2);
            $business = Business::where('id', $id)->first();
            if (!empty($business)) {
                $company_settings = getCompanyAllSetting($business->created_by, $business->workspace);
                $cardPayment = CardPayment::cardPaymentData($business->id);
                $cardPayment_content = [];
                if (!empty($cardPayment->content)) {
                    $cardPayment_content = json_decode($cardPayment->content);
                }
                if ((isset($company_settings['paypal_payment_is_on']) ? $company_settings['paypal_payment_is_on'] : 'off') == 'on' && !empty($company_settings['company_paypal_client_id']) && !empty($company_settings['company_paypal_secret_key'])) {
                    $view->getFactory()->startPush('vcard_payment', view('paypal::payment.vcard_theme_payment', compact('cardPayment_content', 'id')));
                }
            }
        });


        view()->composer(['vcard::card.*.index'], function ($view) {
            $slug = \Request::segment(2);
            if (!is_numeric($slug)) {
                $business = Business::where('slug', $slug)->first();
                if (!empty($business)) {
                    $company_settings = getCompanyAllSetting($business->created_by, $business->workspace);
                    $cardPayment = CardPayment::cardPaymentData($business->id);
                    $cardPayment_content = [];
                    if (!empty($cardPayment->content)) {
                        $cardPayment_content = json_decode($cardPayment->content);
                    }

                    if ((isset($company_settings['paypal_payment_is_on']) ? $company_settings['paypal_payment_is_on'] : 'off') == 'on' && !empty($company_settings['company_paypal_client_id']) && !empty($company_settings['company_paypal_secret_key'])) {
                        $view->getFactory()->startPush('vcard_theme_payment', view('paypal::payment.vcard_payment_booking', compact('business', 'cardPayment_content')));
                    }
                }
            }
        });
    }

    public function register() {}

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
