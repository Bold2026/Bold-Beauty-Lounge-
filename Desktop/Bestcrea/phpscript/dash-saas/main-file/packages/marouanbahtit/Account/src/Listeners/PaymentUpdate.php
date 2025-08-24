<?php

namespace Workdo\Account\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\Account\Entities\AccountUtility;
use Workdo\Account\Entities\BankAccount;
use Workdo\Account\Entities\ChartOfAccount;
use Workdo\Account\Events\UpdatePayment;

class PaymentUpdate
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

    public function handle(UpdatePayment $event)
    {
        if (module_is_active('Account')) {

            $payment = $event->payment;

            $account     = BankAccount::find($payment->account_id);
            $get_account = ChartOfAccount::find($account->chart_account_id);
            $data        = [
                'account_id'         => !empty($get_account)? $get_account->id : 0,
                'transaction_type'   => 'credit',
                'transaction_amount' => $payment->amount,
                'reference'          => 'Payment',
                'reference_id'       => $payment->id,
                'reference_sub_id'   => 0,
                'date'               => $payment->date,
            ];
            AccountUtility::addTransactionLines($data , 'edit' , 'notes');

            $account = ChartOfAccount::where('name','Accounts Payable')->where('workspace' , getActiveWorkSpace())->where('created_by' , creatorId())->first();
            $data    = [
                'account_id'         => !empty($account) ? $account->id : 0,
                'transaction_type'   => 'debit',
                'transaction_amount' => $payment->amount,
                'reference'          => 'Payment',
                'reference_id'       => $payment->id,
                'reference_sub_id'   => 0,
                'date'               => $payment->date,
            ];
            AccountUtility::addTransactionLines($data , 'edit');
        }
    }
}
