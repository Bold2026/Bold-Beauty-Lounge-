<?php

namespace Workdo\Account\Listeners;

use App\Events\DestroyPurchase;
use App\Models\PurchaseDebitNote;
use App\Models\PurchasePayment;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\Account\Entities\AddTransactionLine;
use Workdo\Account\Entities\CustomerDebitNotes;

class PurchaseDestroy
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

    public function handle(DestroyPurchase $event)
    {
        if (module_is_active('Account')) {

            $purchase       = $event->purchase;
            $debitNoteApply = PurchaseDebitNote::where('purchase', $purchase->id)->get();
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
            $purchase_payments = PurchasePayment::where('purchase_id',$purchase->id)->get();

            foreach($purchase_payments as $purchase_payment)
            {
                AddTransactionLine::where('reference_id', $purchase->id)->where('reference_sub_id', $purchase_payment->id)->where('reference', 'Purchase Payment')->delete();
            }
            
            AddTransactionLine::where('reference_id', $purchase->id)->delete();
        }
    }
}
