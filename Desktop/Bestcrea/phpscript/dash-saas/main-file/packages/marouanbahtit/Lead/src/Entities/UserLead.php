<?php

namespace Workdo\Lead\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserLead extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'lead_id',
    ];

    public function getLeadUser()
    {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }

}
