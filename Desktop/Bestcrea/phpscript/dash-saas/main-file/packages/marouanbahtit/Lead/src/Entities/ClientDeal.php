<?php

namespace Workdo\Lead\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ClientDeal extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id', 'deal_id'
    ];

}
