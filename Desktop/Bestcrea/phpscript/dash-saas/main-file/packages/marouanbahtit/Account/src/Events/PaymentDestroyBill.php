<?php

namespace Workdo\Account\Events;

use Illuminate\Queue\SerializesModels;

class PaymentDestroyBill
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $bill;
    public $payment;

    public function __construct($bill , $payment)
    {
        $this->bill    = $bill;
        $this->payment = $payment;
    }
}
