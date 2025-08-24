<?php

namespace Workdo\Account\Listeners;

use App\Events\CreatePaymentPurchase;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Workdo\Account\Entities\AccountUtility;
use Workdo\Account\Entities\BankAccount;
use Workdo\Account\Entities\ChartOfAccount;

class PurchasePaymentCreate
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

    public function handle(CreatePaymentPurchase $event)
    {
        if (module_is_active('Account')) {

            $request     = $event->request;
            $payment     = $event->payment;
            $purchase    = $event->purchase;
            $account     = BankAccount::find($request->account_id);
            $get_account = ChartOfAccount::find($account->chart_account_id);
            $data        = [
                'account_id'         => !empty($get_account)? $get_account->id : 0,
                'transaction_type'   => 'credit',
                'transaction_amount' => $request->amount,
                'reference'          => 'Purchase Payment',
                'reference_id'       => $purchase->id,
                'reference_sub_id'   => $payment->id,
                'date'               => $request->date,
            ];
            AccountUtility::addTransactionLines($data);

            $account = ChartOfAccount::where('name','Accounts Payable')->where('workspace' , getActiveWorkSpace())->where('created_by' , creatorId())->first();
            $data    = [
                'account_id'         => !empty($account) ? $account->id : 0,
                'transaction_type'   => 'debit',
                'transaction_amount' => $request->amount,
                'reference'          => 'Purchase Payment',
                'reference_id'       => $purchase->id,
                'reference_sub_id'   => $payment->id,
                'date'               => $request->date,
            ];
            AccountUtility::addTransactionLines($data);
        }
    }
}
