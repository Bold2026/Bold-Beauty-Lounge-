<?php

namespace Workdo\Hrm\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EventEmployee extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'employee_id',
        'user_id',
        'workspace',
        'created_by',
    ];
    
    protected static function newFactory()
    {
        return \Workdo\Hrm\Database\factories\EventEmployeeFactory::new();
    }
}
