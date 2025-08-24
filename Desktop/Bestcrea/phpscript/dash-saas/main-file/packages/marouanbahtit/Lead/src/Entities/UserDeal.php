<?php

namespace Workdo\Lead\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserDeal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'deal_id',
    ];

    public function getDealUser()
    {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }
}
