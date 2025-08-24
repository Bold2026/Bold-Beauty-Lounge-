<?php

namespace Workdo\Account\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CreditNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice',
        'credit_note',
        'customer',
        'amount',
        'date',
    ];

    public function creditNote()
    {
        return $this->hasOne(CustomerCreditNotes::class, 'id', 'credit_note');
    }

}
