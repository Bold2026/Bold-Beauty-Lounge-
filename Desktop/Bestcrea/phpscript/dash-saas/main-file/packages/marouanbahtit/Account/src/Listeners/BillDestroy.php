<?php

namespace Workdo\Account\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\Account\Entities\AddTransactionLine;
use Workdo\Account\Entities\BillPayment;
use Workdo\Account\Entities\CustomerDebitNotes;
use Workdo\Account\Entities\DebitNote;
use Workdo\Account\Events\DestroyBill;
use Workdo\Account\Entities\TransactionLines;

class BillDestroy
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

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(DestroyBill $event)
    {
        if (module_is_active('Account')) {

            $bill           = $event->bill;
            $debitNoteApply = DebitNote::where('bill', $bill->id)->get();
            foreach($debitNoteApply as $debitNote)
            {
                $customerDebitNote = CustomerDebitNotes::find($debitNote->debit_note);

                $usedDebitNote = $customerDebitNote->usedDebitNote($customerDebitNote) - $debitNote->amount;
                
                if($usedDebitNote == $customerDebitNote->amount)
                {
                    $customerDebitNote->status = 2;
                    $customerDebitNote->save();
                }
                else if($usedDebitNote == 0)
                {
                    $customerDebitNote->status = 0;
                    $customerDebitNote->save();
                }
                else
                {
                    $customerDebitNote->status = 1;
                    $customerDebitNote->save();
                }
                $debitNote->delete();
            }
            $bill_payments = BillPayment::where('bill_id',$bill->id)->get();
            
            foreach($bill_payments as $bill_payment)
            {
                AddTransactionLine::where('reference_id', $bill->id)->where('reference_sub_id', $bill_payment->id)->where('reference', 'Bill Payment')->delete();
            }
            AddTransactionLine::where('reference_id', $bill->id)->delete();
        }
    }
}
