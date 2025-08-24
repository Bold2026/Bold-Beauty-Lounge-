<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Workdo\Account\Entities\Customer;
use Workdo\Account\Entities\CustomerDebitNotes;
use Workdo\Account\Entities\Vender;

class PurchaseDebitNote extends Model
{
    use HasFactory;
    protected $fillable = [
        'purchase',
        'vendor',
        'amount',
        'date',
    ];


    public static function updateUserBalance($users, $id, $amount, $type)
    {
        if($users == 'customer')
        {
            $user = Customer::find($id);
        }
        else
        {
            $user = Vender::find($id);
        }
        if(!empty($user))
        {
            if($type == 'credit')
            {
                $oldBalance    = $user->balance;
                $userBalance = $oldBalance - $amount;
                $user->balance = $userBalance;
                $user->save();
            }
            elseif($type == 'debit')
            {
                $oldBalance    = $user->balance;
                $userBalance = $oldBalance + $amount;
                $user->balance = $userBalance;
                $user->save();
            }
        }
    }

    public function debitNote()
    {
        return $this->hasOne(CustomerDebitNotes::class, 'id', 'debit_note');
    }
}
