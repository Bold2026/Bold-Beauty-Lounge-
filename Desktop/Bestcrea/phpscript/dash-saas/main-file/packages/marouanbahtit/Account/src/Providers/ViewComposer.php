<?php

namespace Workdo\Account\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Workdo\Account\Entities\ChartOfAccount;
use Workdo\Account\Entities\ChartOfAccountSubType;
use Workdo\Account\Entities\ChartOfAccountType;

class ViewComposer extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function boot()
    {
        view()->composer(['product-service::product_section','product-service::service_section','restaurant-menu::item.section'], function ($view) {
            if (\Auth::check() && \Auth::user()->type != 'super admin') {
                $productService = $view->productService;
                $active_module =  ActivatedModule();
                $dependency = explode(',', 'Account');

                $incomeTypes = ChartOfAccountType::where('created_by', '=', creatorId())
                    ->where('workspace', getActiveWorkSpace())
                    ->whereIn('name', ['Assets', 'Liabilities', 'Income'])
                    ->get();

                $incomeChartAccounts = [];

                foreach ($incomeTypes as $type) {
                    $accountTypes = ChartOfAccountSubType::where('type', $type->id)
                        ->where('created_by', '=', creatorId())
                        ->whereNotIn('name', ['Accounts Receivable' , 'Accounts Payable'])
                        ->get();

                    $temp = [];

                    foreach ($accountTypes as $accountType) {
                        $chartOfAccounts = ChartOfAccount::where('sub_type', $accountType->id)->where('parent', '=', 0)
                            ->where('created_by', '=', creatorId())
                            ->get();

                        $incomeSubAccounts = ChartOfAccount::where('sub_type', $accountType->id)->where('parent', '!=', 0)
                        ->where('created_by', '=', creatorId())
                        ->get();

                        $tempData = [
                            'account_name' => $accountType->name,
                            'chart_of_accounts' => [],
                            'subAccounts' => [],
                        ];
                        foreach ($chartOfAccounts as $chartOfAccount) {
                            $tempData['chart_of_accounts'][] = [
                                'id' => $chartOfAccount->id,
                                'account_number' => $chartOfAccount->account_number,
                                'account_name' => $chartOfAccount->name,
                            ];
                        }

                        foreach ($incomeSubAccounts as $chartOfAccount) {
                            $tempData['subAccounts'][] = [
                                'id' => $chartOfAccount->id,
                                'account_number' => $chartOfAccount->account_number,
                                'account_name' => $chartOfAccount->name,
                                'parent'=>$chartOfAccount->parent
                            ];
                        }
                        $temp[$accountType->id] = $tempData;
                    }

                    $incomeChartAccounts[$type->name] = $temp;
                }

                $expenseTypes = ChartOfAccountType::where('created_by', '=', creatorId())
                ->where('workspace', getActiveWorkSpace())
                ->whereIn('name', ['Assets', 'Liabilities', 'Expenses', 'Costs of Goods Sold'])
                ->get();

                $expenseChartAccounts = [];

                foreach ($expenseTypes as $type) {
                    $accountTypes = ChartOfAccountSubType::where('type', $type->id)
                        ->where('created_by', '=', creatorId())
                        ->whereNotIn('name', ['Accounts Receivable' , 'Accounts Payable'])
                        ->get();

                    $temp = [];

                    foreach ($accountTypes as $accountType) {
                        $chartOfAccounts = ChartOfAccount::where('sub_type', $accountType->id)->where('parent', '=', 0)
                            ->where('created_by', '=', creatorId())
                            ->get();

                        $expenseSubAccounts = ChartOfAccount::where('sub_type', $accountType->id)->where('parent', '!=', 0)
                        ->where('created_by', '=', creatorId())
                        ->get();

                        $tempData = [
                            'account_name' => $accountType->name,
                            'chart_of_accounts' => [],
                            'subAccounts' => [],
                        ];
                        foreach ($chartOfAccounts as $chartOfAccount) {
                            $tempData['chart_of_accounts'][] = [
                                'id' => $chartOfAccount->id,
                                'account_number' => $chartOfAccount->account_number,
                                'account_name' => $chartOfAccount->name,
                            ];
                        }

                        foreach ($expenseSubAccounts as $chartOfAccount) {
                            $tempData['subAccounts'][] = [
                                'id' => $chartOfAccount->id,
                                'account_number' => $chartOfAccount->account_number,
                                'account_name' => $chartOfAccount->name,
                                'parent'=>$chartOfAccount->parent
                            ];
                        }
                        $temp[$accountType->id] = $tempData;
                    }

                    $expenseChartAccounts[$type->name] = $temp;
                }

                if (!empty(array_intersect($dependency, $active_module))) {
                    $view->getFactory()->startPush('add_column_in_productservice', view('account::setting.add_column_table', compact('incomeChartAccounts', 'expenseChartAccounts', 'productService')));
                }
            }
        });
        view()->composer(['invoice.create', 'invoice.edit', 'invoice.index', 'invoice.grid'], function ($view) {
            if (Auth::check() && module_is_active('Account')) {
                $view->getFactory()->startPush('account_type', view('account::invoice.account_type'));
            }
        });
        view()->composer(['reminder::reminder.create', 'reminder::reminder.edit'], function ($view) {

            if (Auth::check() && module_is_active('Account')) {
                $view->getFactory()->startPush('module_name', view('account::bill.module_name'));
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
