<?php

namespace MarouanBahtit\Stripe\Events;

use Illuminate\Queue\SerializesModels;

class StripeWaterParkBookingsData
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */

    public $data;
    public $workspace;

    public function __construct($data ,$workspace)
    {
        $this->data = $data;
        $this->workspace = $workspace;
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
