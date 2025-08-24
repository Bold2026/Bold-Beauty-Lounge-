<?php

namespace Workdo\Lead\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DealCall extends Model
{
    use HasFactory;

    protected $fillable = [
        'deal_id',
        'subject',
        'call_type',
        'duration',
        'user_id',
        'description',
        'call_result',
    ];

    public function getDealCallUser()
    {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }
}
