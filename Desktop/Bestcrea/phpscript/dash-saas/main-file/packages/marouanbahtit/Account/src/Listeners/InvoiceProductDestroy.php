<?php

namespace Workdo\Account\Listeners;

use App\Events\ProductDestroyInvoice;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\Account\Entities\AddTransactionLine;
use Workdo\Account\Entities\TransactionLines;

class InvoiceProductDestroy
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
    public function handle(ProductDestroyInvoice $event)
    {
        if (module_is_active('Account')) {

            $invoiceProduct = $event->request;
            AddTransactionLine::where('reference_id', $invoiceProduct->invoice_id)->where('reference_sub_id', $invoiceProduct->product_id)->where('reference', 'Invoice')->delete();
        }
    }
}
