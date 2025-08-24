<?php

namespace Workdo\Account\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BillAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'chart_account_id',
        'price',
        'description',
        'type',
        'ref_id',
        'workspace',
        'created_by',
    ];

   
}
