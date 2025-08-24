<?php

namespace Workdo\Account\Listeners;

use App\Events\CreatePaymentInvoice;
use App\Models\InvoicePayment;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\Account\Entities\AccountUtility;
use Workdo\Account\Entities\BankAccount;
use Workdo\Account\Entities\ChartOfAccount;

class InvoicePaymentCreate
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
     * @param object $event
     * @return void
     */
    public function handle(CreatePaymentInvoice $event)
    {
        if (module_is_active('Account')) {

            $request = $event->request;
            $invoice = $event->invoice;

            $account     = BankAccount::find($request->account_id);
            $get_account = ChartOfAccount::find($account->chart_account_id);
            if(!empty($get_account))
            {
                $data = [
                    'account_id'         => !empty($get_account)? $get_account->id : 0,
                    'transaction_type'   => 'debit',
                    'transaction_amount' => $request->amount,
                    'reference'          => 'Invoice Payment',
                    'reference_id'       => $invoice->id,
                    'reference_sub_id'   => $request->id,
                    'date'               => $request->date,
                ];
                AccountUtility::addTransactionLines($data);
            }

            $account = ChartOfAccount::where('name','Accounts Receivable')->where('workspace' , getActiveWorkSpace())->where('created_by' , creatorId())->first();
            $data    = [
                'account_id'         => !empty($account) ? $account->id : 0,
                'transaction_type'   => 'credit',
                'transaction_amount' => $request->amount,
                'reference'          => 'Invoice Payment',
                'reference_id'       => $invoice->id,
                'reference_sub_id'   => $request->id,
                'date'               => $request->date,
            ];
            AccountUtility::addTransactionLines($data);
        }
    }
}
