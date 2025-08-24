<?php

namespace Workdo\Account\Listeners;

use App\Models\InvoicePayment;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\Account\Entities\AccountUtility;
use Workdo\Account\Entities\BankAccount;
use Workdo\Account\Entities\ChartOfAccount;
use Workdo\Retainer\Events\RetainerConvertToInvoice;

class RetainerPaymentCreate
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

    public function handle(RetainerConvertToInvoice $event)
    {
        if (module_is_active('Account')) {

            $convertInvoice = $event->convertInvoice;

            $invoicePayments = InvoicePayment::where('invoice_id', $convertInvoice->id)->get();
            
            if(!empty($invoicePayments))
            {
                foreach($invoicePayments as $invoicePayment)
                {
                    if($invoicePayment->payment_type == 'Bank Account' || $invoicePayment->payment_type == 'Manually')
                    {
                        $account = BankAccount::where(['created_by'=>$convertInvoice->created_by,'workspace'=>$convertInvoice->workspace])->where('id',$invoicePayment->account_id)->first();
                    }
                    else
                    {
                        $account = BankAccount::where(['created_by'=>$convertInvoice->created_by,'workspace'=>$convertInvoice->workspace])->where('payment_name',$invoicePayment->payment_type)->first();
                    }
                    $get_account = ChartOfAccount::find($account->chart_account_id);
                    if(!empty($get_account))
                    {
                        $data = [
                            'account_id'         => !empty($get_account)? $get_account->id : 0,
                            'transaction_type'   => 'debit',
                            'transaction_amount' => $invoicePayment->amount,
                            'reference'          => 'Invoice Payment',
                            'reference_id'       => $convertInvoice->id,
                            'reference_sub_id'   => $invoicePayment->id,
                            'date'               => $invoicePayment->date,
                        ];
                        AccountUtility::addTransactionLines($data);
                    }
            
                    $account = ChartOfAccount::where('name','Accounts Receivable')->where('workspace' , getActiveWorkSpace())->where('created_by' , creatorId())->first();
                    $data    = [
                        'account_id'         => !empty($account) ? $account->id : 0,
                        'transaction_type'   => 'credit',
                        'transaction_amount' => $invoicePayment->amount,
                        'reference'          => 'Invoice Payment',
                        'reference_id'       => $convertInvoice->id,
                        'reference_sub_id'   => $invoicePayment->id,
                        'date'               => $invoicePayment->date,
                    ];
                    AccountUtility::addTransactionLines($data);
                }
            }
        }
    }
}
