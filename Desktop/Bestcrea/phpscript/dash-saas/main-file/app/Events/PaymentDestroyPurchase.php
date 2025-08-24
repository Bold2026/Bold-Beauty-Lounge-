<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentDestroyPurchase
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $purchase;
    public $payment;

    public function __construct($purchase , $payment)
    {
        $this->purchase = $purchase;
        $this->payment  = $payment;
    }
}
