<?php

namespace Workdo\Account\Events;

use Illuminate\Queue\SerializesModels;

class CreateCustomerCreditNote
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $request;
    public $credit;

    public function __construct($request , $credit)
    {
        $this->request = $request;
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
