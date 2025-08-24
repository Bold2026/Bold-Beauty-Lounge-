<?php

namespace Workdo\Account\Listeners;

use App\Events\DestroyInvoice;
use App\Models\InvoicePayment;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\Account\Entities\AddTransactionLine;
use Workdo\Account\Entities\CreditNote;
use Workdo\Account\Entities\CustomerCreditNotes;
use Workdo\Account\Entities\TransactionLines;

class InvoiceDestroy
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
    public function handle(DestroyInvoice $event)
    {
        if (module_is_active('Account')) {
        
            $invoice         = $event->invoice;
            $creditNoteApply = CreditNote::where('invoice', $invoice->id)->get();
            foreach($creditNoteApply as $creditNote)
            {
                $customerCreditNote = CustomerCreditNotes::find($creditNote->credit_note);

                $usedCreditNote = $customerCreditNote->usedCreditNote() - $creditNote->amount;
                
                if($usedCreditNote == $customerCreditNote->amount)
                {
                    $customerCreditNote->status = 2;
                    $customerCreditNote->save();
                }
                else if($usedCreditNote == 0)
                {
                    $customerCreditNote->status = 0;
                    $customerCreditNote->save();
                }
                else
                {
                    $customerCreditNote->status = 1;
                    $customerCreditNote->save();
                }
                $creditNote->delete();
            }
            $invoice_payments = InvoicePayment::where('invoice_id',$invoice->id)->get();

            foreach($invoice_payments as $invoice_payment)
            {
                AddTransactionLine::where('reference_id', $invoice->id)->where('reference_sub_id', $invoice_payment->id)->where('reference', 'Invoice Payment')->delete();
            }
            
            AddTransactionLine::where('reference_id', $invoice->id)->delete();
        }
    }
}
