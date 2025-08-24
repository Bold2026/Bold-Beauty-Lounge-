<?php

namespace Workdo\Account\Listeners;

use App\Events\SentInvoice;
use App\Models\InvoicePayment;
use App\Models\InvoiceProduct;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\Account\Entities\AccountUtility;
use Workdo\Account\Entities\ChartOfAccount;

class InvoiceSent
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
     * @param object $event
     * @return void
     */
    public function handle($event)
    {
        if (module_is_active('Account')) {

            if(isset($event->invoice))
            {
                $invoice = $event->invoice;
            }
            else
            {
                $invoice = $event->convertInvoice;
            }

            $invoice_products = InvoiceProduct::where('invoice_id', $invoice->id)->get();

            foreach ($invoice_products as $invoice_product) {
                $product       = \Workdo\ProductService\Entities\ProductService::find($invoice_product->product_id);
                $totalTaxPrice = 0;
                if ($invoice->invoice_module == 'newspaper'){
                    $taxes = \Workdo\Newspaper\Entities\NewspaperTax::tax($invoice_product->tax);
                    foreach ($taxes as $tax) {
                        if(!empty($tax)){
                            $taxPrice = \Workdo\Newspaper\Entities\NewspaperTax::taxRate($tax->percentage, $invoice_product->price, $invoice_product->quantity);
                            $totalTaxPrice += $taxPrice;
                        }
                    }
                }
                else {                    
                    $taxes         = \App\Models\Invoice::tax($invoice_product->tax);
                    foreach ($taxes as $tax) {
                        if(!empty($tax)){
                            $taxPrice       = \App\Models\Invoice::taxRate($tax->rate, $invoice_product->price, $invoice_product->quantity, $invoice_product->discount);
                            $totalTaxPrice += $taxPrice;
                        }
                    }
                }
                if($invoice_product->quantity == 0){
                    $quantity = 1;
                }   
                else {
                    $quantity = $invoice_product->quantity;
                }
                if($invoice->invoice_module == 'machinerepair' || $invoice->invoice_module == 'mobileservice' || $invoice->invoice_module == 'vehicleinspection') {
                    $itemAmount = ($invoice_product->price * $quantity) - ($invoice_product->discount) + $totalTaxPrice + $invoice->category_id;
                }
                else if ($invoice->invoice_module == 'Fleet') {
                    $itemAmount = ($invoice_product->price * $quantity) - ($invoice_product->discount) + $totalTaxPrice;
                }
                else {
                    $itemAmount = ($invoice_product->price * $quantity) - ($invoice_product->discount) + $totalTaxPrice;
                }
                $data       = [
                    'account_id'         => (!empty($product->sale_chartaccount_id) && $invoice->invoice_module != 'cardealership' && $invoice->invoice_module != 'lms' && $invoice->invoice_module != 'newspaper' && $invoice->invoice_module != 'Fleet') ? $product->sale_chartaccount_id : (!empty($invoice->account_id) ? $invoice->account_id : ''),
                    'transaction_type'   => 'credit',
                    'transaction_amount' => $itemAmount,
                    'reference'          => 'Invoice',
                    'reference_id'       => $invoice->id,
                    'reference_sub_id'   => $invoice_product->id,
                    'date'               => $invoice->issue_date,
                ];
                AccountUtility::addTransactionLines($data);

                $account = ChartOfAccount::where('name','Accounts Receivable')->where('workspace' , getActiveWorkSpace())->where('created_by' , creatorId())->first();
                $data    = [
                    'account_id'         => !empty($account) ? $account->id : 0,
                    'transaction_type'   => 'debit',
                    'transaction_amount' => $itemAmount,
                    'reference'          => 'Invoice',
                    'reference_id'       => $invoice->id,
                    'reference_sub_id'   => $invoice_product->id,
                    'date'               => $invoice->issue_date,
                ];
                AccountUtility::addTransactionLines($data);
            }        
        }
    }
}
