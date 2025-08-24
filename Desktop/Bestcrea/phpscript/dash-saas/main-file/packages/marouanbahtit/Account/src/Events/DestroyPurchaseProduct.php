<?php

namespace Workdo\Account\Events;

use Illuminate\Queue\SerializesModels;

class DestroyPurchaseProduct
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $request;
    public $purchaseProduct;

    public function __construct($request , $purchaseProduct)
    {
        $this->request         = $request;
        $this->purchaseProduct = $purchaseProduct;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [];
    }
}
