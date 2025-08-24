<?php

namespace Workdo\Account\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\Account\Entities\AccountUtility;
use Workdo\Account\Entities\Bill;
use Workdo\Account\Entities\BillAccount;
use Workdo\Account\Entities\BillProduct;
use Workdo\Account\Entities\ChartOfAccount;
use Workdo\Account\Events\SentBill;
use Workdo\ProductService\Entities\ProductService;

class BillSent
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(SentBill $event)
    {
        if (module_is_active('Account')) {

            $bill          = $event->bill;
            $bill_products = BillProduct::where('bill_id', $bill->id)->get();

            foreach ($bill_products as $bill_product) {
                $product       = ProductService::find($bill_product->product_id);
                $totalTaxPrice = 0;
                $taxes         = AccountUtility::tax($bill_product->tax);

                foreach ($taxes as $tax) {
                    $taxPrice       = AccountUtility::taxRate($tax['rate'], $bill_product->price, $bill_product->quantity, $bill_product->discount);
                    $totalTaxPrice += $taxPrice;
                }
                
                $itemAmount = ($bill_product->price * $bill_product->quantity) - ($bill_product->discount) + $totalTaxPrice;
                $data       = [
                    'account_id'         => !empty($product->expense_chartaccount_id) ? $product->expense_chartaccount_id : (!empty($bill->account_id) ? $bill->account_id : ''),
                    'transaction_type'   => 'debit',
                    'transaction_amount' => $itemAmount,
                    'reference'          => 'Bill',
                    'reference_id'       => $bill->id,
                    'reference_sub_id'   => $bill_product->id,
                    'date'               => $bill->bill_date,
                ];
                AccountUtility::addTransactionLines($data);

                $account = ChartOfAccount::where('name','Accounts Payable')->where('workspace' , getActiveWorkSpace())->where('created_by' , creatorId())->first();
                $data    = [
                    'account_id'         => !empty($account) ? $account->id : 0,
                    'transaction_type'   => 'credit',
                    'transaction_amount' => $itemAmount,
                    'reference'          => 'Bill',
                    'reference_id'       => $bill->id,
                    'reference_sub_id'   => $bill_product->id,
                    'date'               => $bill->bill_date,
                ];
                AccountUtility::addTransactionLines($data);
            }
        }
    }
}
