<?php

namespace Workdo\Account\Events;

use Illuminate\Queue\SerializesModels;

class DestroyCustomerCreditNote
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $credit;

    public function __construct($credit)
    {
        $this->credit  = $credit;
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
