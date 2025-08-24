<?php

namespace Workdo\Account\Listeners;

// use App\Events\ProductDestroyBill;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\Account\Entities\AddTransactionLine;
use Workdo\Account\Entities\BillAccount;
use Workdo\Account\Entities\TransactionLines;
use Workdo\Account\Events\ProductDestroyBill;

class BillProductDestroy
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
    public function handle(ProductDestroyBill $event)
    {
        if (module_is_active('Account')) {

            $billProduct = $event->bill;
            AddTransactionLine::where('reference_id', $billProduct->bill_id)->where('reference_sub_id', $billProduct->product_id)->where('reference', 'Bill')->delete();
        }
    }
}
