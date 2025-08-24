<?php

namespace Workdo\Account\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\Account\Entities\AddTransactionLine;
use Workdo\Account\Events\DestroyCustomerDebitNote;

class CustomerDebitNoteDestroy
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

    public function handle(DestroyCustomerDebitNote $event)
    {
        if (module_is_active('Account')) {

            $debit = $event->debitNote;

            AddTransactionLine::where('reference_id',$debit->id)->where('reference_sub_id',$debit->bill)->where('reference', 'Debit Note')->delete();
        }
    }
}
