<?php

namespace Workdo\Hrm\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class IpRestrict extends Model
{
    use HasFactory;

    protected $fillable = [
        'ip',
        'workspace',
        'created_by'
    ];
    
    protected static function newFactory()
    {
        return \Workdo\Hrm\Database\factories\IpRestrictFactory::new();
    }
}
