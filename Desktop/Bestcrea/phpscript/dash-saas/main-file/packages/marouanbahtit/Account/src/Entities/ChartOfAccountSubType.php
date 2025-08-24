<?php

namespace Workdo\Account\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChartOfAccountSubType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'workspace',
        'created_by',
    ];


}
