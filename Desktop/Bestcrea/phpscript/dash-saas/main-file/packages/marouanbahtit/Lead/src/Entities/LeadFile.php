<?php

namespace Workdo\Lead\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LeadFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'file_name',
        'file_path',
    ];

}
