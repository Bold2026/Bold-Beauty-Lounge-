<?php

namespace Workdo\Account\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BankAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'chart_account_id',
        'holder_name',
        'payment_name',
        'bank_name',
        'account_number',
        'opening_balance',
        'contact_number',
        'bank_address',
        'workspace',
        'created_by',
    ];


}
