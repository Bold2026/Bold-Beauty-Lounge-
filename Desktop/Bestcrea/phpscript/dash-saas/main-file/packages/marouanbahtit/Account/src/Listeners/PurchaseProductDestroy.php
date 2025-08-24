<?php

namespace Workdo\Account\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\Account\Entities\AddTransactionLine;
use Workdo\Account\Events\DestroyPurchaseProduct;

class PurchaseProductDestroy
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

    public function handle(DestroyPurchaseProduct $event)
    {
        if (module_is_active('Account')) {

            $purchaseProduct = $event->purchaseProduct;
            AddTransactionLine::where('reference_id', $purchaseProduct->purchase_id)->where('reference_sub_id', $purchaseProduct->id)->where('reference', 'Purchase')->delete();
        }
    }
}
