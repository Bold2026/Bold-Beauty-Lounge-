<?php

namespace Workdo\Account\Listeners;

use Workdo\Account\Entities\AccountUtility;
use Workdo\Account\Entities\BankAccount;
use Workdo\Account\Entities\ChartOfAccount;

class InvoiceOnlinePayamentCreate
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

    public function handle($event)
    {
        $invoice = $event->data;
        if ($event->type == 'invoice' && module_is_active('Account' , $invoice->created_by)) {

            $payment = $event->payment;

                if($payment->payment_type == 'Bank Account')
                {
                    $account = BankAccount::where(['created_by'=>$invoice->created_by,'workspace'=>$invoice->workspace])->where('id',$payment->account_id)->first();
                }
                else
                {
                    $account = BankAccount::where(['created_by'=>$invoice->created_by,'workspace'=>$invoice->workspace])->where('payment_name',$payment->payment_type)->first();
                }
                $get_account = ChartOfAccount::find($account->chart_account_id);
                if(!empty($get_account))
                {
                    $data = [
                        'account_id'         => !empty($get_account)? $get_account->id : 0,
                        'transaction_type'   => 'debit',
                        'transaction_amount' => $payment->amount,
                        'reference'          => 'Invoice Payment',
                        'reference_id'       => $invoice->id,
                        'reference_sub_id'   => $payment->id,
                        'date'               => $payment->date,
                        'workspace'          => $invoice->workspace,
                        'created_by'         => $invoice->created_by
                    ];
                    AccountUtility::addTransactionLines($data);
                }

                $account = ChartOfAccount::where('name','Accounts Receivable')->where('workspace' , $invoice->workspace)->where('created_by' , $invoice->created_by)->first();
                $data    = [
                    'account_id'         => !empty($account) ? $account->id : 0,
                    'transaction_type'   => 'credit',
                    'transaction_amount' => $payment->amount,
                    'reference'          => 'Invoice Payment',
                    'reference_id'       => $invoice->id,
                    'reference_sub_id'   => $payment->id,
                    'date'               => $payment->date,
                    'workspace'          => $invoice->workspace,
                    'created_by'         => $invoice->created_by
                ];
                AccountUtility::addTransactionLines($data);
        }
    }
}
