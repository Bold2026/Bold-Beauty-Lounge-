<?php

namespace Workdo\Account\Listeners;

use App\Events\SentPurchase;
use App\Models\PurchaseProduct;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\Account\Entities\AccountUtility;
use Workdo\Account\Entities\ChartOfAccount;
use Workdo\ProductService\Entities\ProductService;

class PurchaseSent
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

    public function handle(SentPurchase $event)
    {
        if (module_is_active('Account')) {

            $purchase          = $event->purchase;
            $purchase_products = PurchaseProduct::where('purchase_id', $purchase->id)->get();

            foreach ($purchase_products as $purchase_product) {
                $product       = ProductService::find($purchase_product->product_id);
                $totalTaxPrice = 0;
                $taxes         = AccountUtility::tax($purchase_product->tax);
                foreach ($taxes as $tax) {
                    $taxPrice       = AccountUtility::taxRate($tax['rate'], $purchase_product->price, $purchase_product->quantity, $purchase_product->discount);
                    $totalTaxPrice += $taxPrice;
                }
                $itemAmount = ($purchase_product->price * $purchase_product->quantity) - ($purchase_product->discount) + $totalTaxPrice;
                $data       = [
                    'account_id'         => $product->expense_chartaccount_id,
                    'transaction_type'   => 'debit',
                    'transaction_amount' => $itemAmount,
                    'reference'          => 'Purchase',
                    'reference_id'       => $purchase->id,
                    'reference_sub_id'   => $purchase_product->id,
                    'date'               => $purchase->purchase_date,
                ];
                AccountUtility::addTransactionLines($data);

                $account = ChartOfAccount::where('name','Accounts Payable')->where('workspace' , getActiveWorkSpace())->where('created_by' , creatorId())->first();
                $data    = [
                    'account_id'         => !empty($account) ? $account->id : 0,
                    'transaction_type'   => 'credit',
                    'transaction_amount' => $itemAmount,
                    'reference'          => 'Purchase',
                    'reference_id'       => $purchase->id,
                    'reference_sub_id'   => $purchase_product->id,
                    'date'               => $purchase->purchase_date,
                ];
                AccountUtility::addTransactionLines($data);
            }
        }
    }
}
