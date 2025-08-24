<?php

namespace Workdo\Lead\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DealEmail extends Model
{
    use HasFactory;

    protected $fillable = [
        'deal_id',
        'to',
        'subject',
        'description',
    ];

}
