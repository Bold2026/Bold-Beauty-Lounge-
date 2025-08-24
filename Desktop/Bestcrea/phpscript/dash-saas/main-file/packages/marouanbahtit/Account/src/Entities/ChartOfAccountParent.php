<?php

namespace Workdo\Account\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChartOfAccountParent extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sub_type',
        'type',
        'account',
        'parent',
        'workspace',
        'created_by',
    ];

}
