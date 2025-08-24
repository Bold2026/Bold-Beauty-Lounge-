<?php

namespace Workdo\Paypal\Providers;

use Illuminate\Support\ServiceProvider;

class BankAccountPayments extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */

    public function boot()
    {
        view()->composer(['account::bankAccount.create','account::bankAccount.edit'], function ($view)
        {
            if(\Auth::check() && module_is_active('Paypal'))
            {
                $data = $view->getData();

                $payment_type = isset($data['bankAccount']) ? $data['bankAccount']->payment_name : null;

                $selected = ($payment_type == 'PayPal') ? 'selected' : '';

                // Developer Note : Please use this value in invoice payment status function  "invoice_payment->payment_type"
                $view->getFactory()->startPush('bank_payments','<option value="PayPal" '.$selected.'>Paypal</option>');
            };
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
