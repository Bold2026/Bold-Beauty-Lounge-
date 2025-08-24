<?php

namespace Workdo\Lead\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LeadEmail extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'to',
        'subject',
        'description',
    ];

}
