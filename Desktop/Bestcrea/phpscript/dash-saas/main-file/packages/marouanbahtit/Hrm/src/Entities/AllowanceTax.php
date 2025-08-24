<?php

namespace Workdo\Hrm\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AllowanceTax extends Model
{
    use HasFactory;

    protected $table = 'allowance_taxs';

    protected $fillable = [
        'description',
        'amount',
        'workspace',
        'created_by',
    ];
    
    protected static function newFactory()
    {
        return \Workdo\Hrm\Database\factories\AllowanceTaxFactory::new();
    }
}
