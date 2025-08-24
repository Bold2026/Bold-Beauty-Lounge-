<?php

namespace Workdo\Account\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CustomerDebitNotes extends Model
{
    use HasFactory;

    protected $fillable = [
        'bill',
        'vendor',
        'amount',
        'date',
        'status',
    ];


    public function custom_vendor()
    {
        return $this->hasOne(\Workdo\Account\Entities\Vender::class, 'id', 'vendor');
    }

    public function bill_number()
    {
        return $this->hasOne(\Workdo\Account\Entities\Bill::class, 'id', 'bill');
    }

    public function purchase_number()
    {
        return $this->hasOne(\App\Models\Purchase::class, 'id', 'bill');
    }

    public function usedDebitNote($debitNote)
    {
        if($debitNote->type == 'bill') {
            return $this->hasMany(\Workdo\Account\Entities\DebitNote::class, 'debit_note', 'id')->sum('amount');
        }
        else {
            return $this->hasMany(\App\Models\PurchaseDebitNote::class, 'debit_note', 'id')->sum('amount');
        }
    }

    public static function debitNumberFormat($number)
    {
        return '#DN' . sprintf("%05d", $number);
    }
    
    public static $statues = [
        'Pending',
        'Partially Used',
        'Fully Used',
    ];

    public static $debit_type = [
        'Bill',
        'Purchase',
    ];
}
