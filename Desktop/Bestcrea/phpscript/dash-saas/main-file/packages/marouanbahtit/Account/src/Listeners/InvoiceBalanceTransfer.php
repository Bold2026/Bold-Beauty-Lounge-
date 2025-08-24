<?php

namespace Workdo\Account\Listeners;

use Exception;
use Workdo\Account\Entities\AccountUtility;
use Workdo\Account\Entities\BankAccount;
use Workdo\Account\Entities\Transfer;
use Illuminate\Http\Request;

class InvoiceBalanceTransfer
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
    public function handle($event)
    {
        $invoice = $event->data;
        if($event->type == 'invoice' && module_is_active('Account',$invoice->created_by , $invoice->workspace)){
            $payment = $event->payment;
            if($payment->payment_type == 'Bank Account' || $payment->payment_type == 'Manually')
            {
                $account = BankAccount::where(['created_by'=>$invoice->created_by,'workspace'=>$invoice->workspace])->where('id',$payment->account_id)->first();
            }
            else
            {
                $account = BankAccount::where(['created_by'=>$invoice->created_by,'workspace'=>$invoice->workspace])->where('payment_name',$payment->payment_type)->first();
            }
            if($account)
            {
                $customerInvoices = ['taskly', 'account', 'cmms', 'cardealership', 'RestaurantMenu', 'rent' , 'Fleet'];
                if(in_array($invoice->invoice_module,$customerInvoices) ){
                    AccountUtility::updateUserBalance('customer', $invoice->customer_id, $payment->amount, 'debit');
                }

                Transfer::bankAccountBalance($account->id, $payment->amount, 'credit');

                if ($invoice->status == 0) {
                    $status = 'Draft';
                } elseif ($invoice->status == 1) {
                    $status = 'Open';
                } elseif ($invoice->status == 2) {
                    $status = 'Unpaid';
                } elseif ($invoice->status == 3) {
                    $status = 'Partialy Paid';
                } elseif ($invoice->status == 4) {
                    $status = 'Paid';
                }
                $request = new Request();
                $request->replace([
                    'account'       => $account->id,
                    'user_id'       => $invoice->user_id,
                    'user_type'     => 'Customer',
                    'type'          => $status,
                    'amount'        => $payment->amount,
                    'description'   => 'Payment with ' . $payment->payment_type,
                    'date'          => $payment->date,
                    'created_by'    => $invoice->created_by,
                    'payment_id'    => $payment->id,
                    'category'      => $event->type,
                    'workspace'     => $invoice->workspace,
                ]);

                \Workdo\Account\Entities\Transaction::addTransaction($request);
            }
            else
            {
                throw new Exception("Payment Successful, But Bank Account not connected with this payment.", 1);
            }
        }
        elseif($event->type == 'retainer' && module_is_active('Account',$invoice->created_by , $invoice->workspace))
        {
            $payment = $event->payment;
            if($payment->payment_type == 'Bank Account' || $payment->payment_type == 'Manually')
            {
                $account = BankAccount::where(['created_by'=>$invoice->created_by,'workspace'=>$invoice->workspace])->where('id',$payment->account_id)->first();
            }
            else
            {
                $account = BankAccount::where(['created_by'=>$invoice->created_by,'workspace'=>$invoice->workspace])->where('payment_name',$payment->payment_type)->first();
            }
            if($account)
            {
                $customerInvoices = ['taskly', 'account', 'cmms', 'cardealership', 'RestaurantMenu', 'rent' , 'Fleet'];

                if(in_array($invoice->retainer_module,$customerInvoices) ){
                    AccountUtility::updateUserBalance('customer', $invoice->customer_id, $payment->amount, 'debit');
                }

                Transfer::bankAccountBalance($account->id, $payment->amount, 'credit');

                if ($invoice->status == 0) {
                    $status = 'Draft';
                } elseif ($invoice->status == 1) {
                    $status = 'Open';
                } elseif ($invoice->status == 2) {
                    $status = 'Accepted';
                } elseif ($invoice->status == 3) {
                    $status = 'Declined';
                } elseif ($invoice->status == 4) {
                    $status = 'Partialy Paid';
                } elseif ($invoice->status == 5) {
                    $status = 'Paid';
                }
                $request = new Request();
                $request->replace([
                    'account'       => $account->id,
                    'user_id'       => $invoice->user_id,
                    'user_type'     => 'Customer',
                    'type'          => $status,
                    'amount'        => $payment->amount,
                    'description'   => 'Payment with ' . $payment->payment_type,
                    'date'          => $payment->date,
                    'created_by'    => $invoice->created_by,
                    'payment_id'    => $payment->id,
                    'category'      => $event->type,
                    'workspace'     => $invoice->workspace,
                ]);
                \Workdo\Account\Entities\Transaction::addTransaction($request);
            }
            else
            {
                throw new Exception("Payment Successful, But Bank Account not connected with this payment.", 1);
            }
        }

    }
}
