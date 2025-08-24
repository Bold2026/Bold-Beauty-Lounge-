<?php

namespace Workdo\Account\Events;

use Illuminate\Queue\SerializesModels;

class UpdateCustomerDebitNote
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public $request;
    public $debit;

    public function __construct($request , $debit)
    {
        $this->request = $request;
        $this->debit   = $debit;
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
