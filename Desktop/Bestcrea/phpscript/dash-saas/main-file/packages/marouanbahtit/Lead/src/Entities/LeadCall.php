<?php

namespace Workdo\Lead\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LeadCall extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'subject',
        'call_type',
        'duration',
        'user_id',
        'description',
        'call_result',
    ];

    public function getLeadCallUser()
    {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }
}
