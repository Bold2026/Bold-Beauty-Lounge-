<?php

namespace Workdo\Account\Listeners;

use App\Events\PaymentDestroyPurchase;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\Account\Entities\AddTransactionLine;

class PurchasePaymentDestroy
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

    public function handle(PaymentDestroyPurchase $event)
    {
        if (module_is_active('Account')) {

            $purchase = $event->purchase;
            $payment  = $event->payment;

            AddTransactionLine::where('reference_id',$purchase->id)->where('reference_sub_id',$payment->id)->where('reference', 'Purchase Payment')->delete();
        }
    }
}
