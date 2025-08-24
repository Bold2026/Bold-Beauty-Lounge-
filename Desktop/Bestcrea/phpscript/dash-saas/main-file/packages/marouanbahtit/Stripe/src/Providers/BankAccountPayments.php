<?php

namespace Workdo\Stripe\Providers;

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
            if(\Auth::check() && module_is_active('Stripe'))
            {
                $data = $view->getData();

                $payment_type = isset($data['bankAccount']) ? $data['bankAccount']->payment_name : null;

                $selected = ($payment_type == 'Stripe') ? 'selected' : '';

                // Developer Note : Please use this value in invoice payment status function  "invoice_payment->payment_type"
                $view->getFactory()->startPush('bank_payments', '<option value="Stripe" '.$selected.'>Stripe</option>');
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
