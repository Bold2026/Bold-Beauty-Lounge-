<?php

namespace App\Traits;

use Workdo\Account\Entities\Customer;
use Workdo\Account\Entities\CustomerCreditNotes;
use Workdo\Account\Entities\CustomerDebitNotes;
use Workdo\Account\Entities\Vender;

trait CreditDebitNoteBalance
{
    public function  updateBalance($users, $id, $amount, $type)
    {
        if ($users == 'customer') {
            $customer = Customer::find($id);
                if(!empty($customer)) {
                    if ($type == 'debit') {
                        $oldBalance = $customer->credit_note_balance;
                        $userBalance = $oldBalance - $amount;
                        $customer->credit_note_balance = $userBalance;
                        $customer->save();
                    } elseif ($type == 'credit') {
                        $oldBalance = $customer->credit_note_balance;
                        $userBalance = $oldBalance + $amount;
                        $customer->credit_note_balance = $userBalance;
                        $customer->save();
                    }
                }
        } else {
            $vendor = Vender::find($id);
            if(!empty($vendor)){
                if ($type == 'debit') {
                    $oldBalance = $vendor->debit_note_balance;
                    $userBalance = $oldBalance - $amount;
                    $vendor->debit_note_balance = $userBalance;
                    $vendor->save();
                } elseif ($type == 'credit') {
                    $oldBalance = $vendor->debit_note_balance;
                    $userBalance = $oldBalance + $amount;
                    $vendor->debit_note_balance = $userBalance;
                    $vendor->save();
                }
            }
        }
    }

    public function updateCreditNoteStatus($customerCreditNote , $status = null)
    {
        if($customerCreditNote != null)
        {
            $creditNote = CustomerCreditNotes::find($customerCreditNote->credit_note);
            if($status == 'delete')
            {
                $usedCreditNote = $creditNote->usedCreditNote() - $customerCreditNote->amount;
            }
            else
            {
                $usedCreditNote = $creditNote->usedCreditNote();
            }
            
            if($usedCreditNote == $creditNote->amount)
            {
                $creditNote->status = 2;
                $creditNote->save();
            }
            else if($usedCreditNote == 0)
            {
                $creditNote->status = 0;
                $creditNote->save();
            }
            else
            {
                $creditNote->status = 1;
                $creditNote->save();
            }
        }
    }

    public function updateDebitNoteStatus($customerDebitNote , $status = null)
    {
        if($customerDebitNote != null)
        {
            $debitNote = CustomerDebitNotes::find($customerDebitNote->debit_note);
            if($status == 'delete')
            {
                $usedDebitNote = $debitNote->usedDebitNote($debitNote) - $customerDebitNote->amount;
            }
            else
            {
                $usedDebitNote = $debitNote->usedDebitNote($debitNote);
            }
            
            if($usedDebitNote == $debitNote->amount)
            {
                $debitNote->status = 2;
                $debitNote->save();
            }
            else if($usedDebitNote == 0)
            {
                $debitNote->status = 0;
                $debitNote->save();
            }
            else
            {
                $debitNote->status = 1;
                $debitNote->save();
            }
        }
    }

}
