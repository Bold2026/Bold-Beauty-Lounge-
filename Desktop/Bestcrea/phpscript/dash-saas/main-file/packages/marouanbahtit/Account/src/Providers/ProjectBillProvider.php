<?php

namespace Workdo\Account\Providers;

use Illuminate\Support\ServiceProvider;

class ProjectBillProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function boot()
    {


        view()->composer(['taskly::layouts.finance_tab'], function ($view)
        {
            if(\Auth::check())
            {
                try {
                    $ids = \Request::segment(2);
                    if(!empty($ids))
                    {
                        try {
                            if(module_is_active('Account'))
                            {
                                $view->getFactory()->startPush('project_bill_tab', view('account::bill.finance' ,compact('ids')));
                            }

                        } catch (\Throwable $th)
                        {
                        }
                    }
                } catch (\Throwable $th) {

                }
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
