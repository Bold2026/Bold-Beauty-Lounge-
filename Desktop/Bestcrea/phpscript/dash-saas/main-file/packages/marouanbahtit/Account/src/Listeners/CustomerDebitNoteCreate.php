<?php

namespace Workdo\Account\Listeners;

use App\Models\Purchase;
use App\Models\PurchaseProduct;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\Account\Entities\AccountUtility;
use Workdo\Account\Entities\Bill;
use Workdo\Account\Entities\BillProduct;
use Workdo\Account\Entities\ChartOfAccount;
use Workdo\Account\Events\CreateCustomerDebitNote;
use Workdo\ProductService\Entities\ProductService;

class CustomerDebitNoteCreate
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

    public function handle(CreateCustomerDebitNote $event)
    {
        if (module_is_active('Account')) {

            $debit = $event->debit;
            $account_id = '';

            $isBill = $debit->type === 'bill';
            $hasProduct = !is_null($debit->bill_product);
            
            if ($hasProduct) {
                $billProduct = $isBill ? BillProduct::find($debit->bill_product) : PurchaseProduct::find($debit->bill_product);
            
                if ($billProduct) {
                    $product = ProductService::find($billProduct->product_id);
                    $account_id = $product->sale_chartaccount_id ?? '';
                }
            } else {
                $parent = $isBill ? Bill::find($debit->bill) : Purchase::find($debit->bill);            
                $account_id = $parent->account_id ?? '';
            }

            $data = [
                'account_id'         => $account_id,
                'transaction_type'   => 'credit',
                'transaction_amount' => $debit->amount,
                'reference'          => 'Debit Note',
                'reference_id'       => $debit->id,
                'reference_sub_id'   => $debit->bill,
                'date'               => $debit->date,
            ];
            AccountUtility::addTransactionLines($data);

            $account = ChartOfAccount::where('name','Accounts Payable')->where('workspace' , getActiveWorkSpace())->where('created_by' , creatorId())->first();
            $data    = [
                'account_id'         => !empty($account) ? $account->id : 0,
                'transaction_type'   => 'debit',
                'transaction_amount' => $debit->amount,
                'reference'          => 'Debit Note',
                'reference_id'       => $debit->id,
                'reference_sub_id'   => $debit->bill,
                'date'               => $debit->date,
            ];
            AccountUtility::addTransactionLines($data);
        }
    }
}
