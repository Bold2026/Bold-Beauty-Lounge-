<?php

namespace Workdo\Account\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\Account\Entities\AddTransactionLine;
use Workdo\Account\Events\DestroyCustomerCreditNote;

class CustomerCreditNoteDestroy
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

    public function handle(DestroyCustomerCreditNote $event)
    {
        if (module_is_active('Account')) {

            $credit = $event->credit;

            AddTransactionLine::where('reference_id',$credit->id)->where('reference_sub_id',$credit->invoice)->where('reference', 'Credit Note')->delete();
        }
    }
}
