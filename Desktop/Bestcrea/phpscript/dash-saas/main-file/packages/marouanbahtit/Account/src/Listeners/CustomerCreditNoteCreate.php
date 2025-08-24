<?php

namespace Workdo\Account\Listeners;

use App\Models\Invoice;
use App\Models\InvoiceProduct;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\Account\Entities\AccountUtility;
use Workdo\Account\Entities\ChartOfAccount;
use Workdo\Account\Events\CreateCustomerCreditNote;
use Workdo\ProductService\Entities\ProductService;

class CustomerCreditNoteCreate
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

    public function handle(CreateCustomerCreditNote $event)
    {
        if (module_is_active('Account')) {
            $credit = $event->credit;
            
            if($credit->invoice_product == null)
            {
                $invoice        = Invoice::find($credit->invoice);
                $account_id     = !empty($invoice->account_id) ? $invoice->account_id:'';
            }
            else {
                $invoiceProduct = InvoiceProduct::find($credit->invoice_product);
                $product        = ProductService::find($invoiceProduct->product_id);
                $account_id     = !empty($product->sale_chartaccount_id) ? $product->sale_chartaccount_id:'';
            }
            
            $data = [
                'account_id'         => $account_id,
                'transaction_type'   => 'debit',
                'transaction_amount' => $credit->amount,
                'reference'          => 'Credit Note',
                'reference_id'       => $credit->id,
                'reference_sub_id'   => $credit->invoice,
                'date'               => $credit->date,
            ];
            AccountUtility::addTransactionLines($data);

            $account = ChartOfAccount::where('name','Accounts Receivable')->where('workspace' , getActiveWorkSpace())->where('created_by' , creatorId())->first();
            $data    = [
                'account_id'         => !empty($account) ? $account->id : 0,
                'transaction_type'   => 'credit',
                'transaction_amount' => $credit->amount,
                'reference'          => 'Credit Note',
                'reference_id'       => $credit->id,
                'reference_sub_id'   => $credit->invoice,
                'date'               => $credit->date,
            ];
            AccountUtility::addTransactionLines($data);
        }
    }
}
