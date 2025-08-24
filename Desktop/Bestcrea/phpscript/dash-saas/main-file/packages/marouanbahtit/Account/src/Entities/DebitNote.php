<?php

namespace Workdo\Account\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DebitNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'bill',
        'debit_note',
        'vendor',
        'amount',
        'date',
    ];


    public function vendor_name()
    {
        return $this->hasOne(Vender::class, 'id', 'vendor');
    }

    public function debitNote()
    {
        return $this->hasOne(CustomerDebitNotes::class, 'id', 'debit_note');
    }
}
