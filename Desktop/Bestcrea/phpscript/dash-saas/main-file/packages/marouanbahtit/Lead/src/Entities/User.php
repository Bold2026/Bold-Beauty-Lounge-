<?php

namespace Workdo\Lead\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Model
{
    use HasFactory;

    public function deals()
    {
        return $this->belongsToMany('Workdo\Lead\Entities\Deal', 'user_deals', 'user_id', 'deal_id');
    }

    public function leads()
    {
        return $this->belongsToMany('Workdo\Lead\Entities\Lead', 'user_leads', 'user_id', 'lead_id');
    }

    public function clientDeals()
    {
        return $this->belongsToMany('Workdo\Lead\Entities\Deal', 'client_deals', 'client_id', 'deal_id');
    }

    public function clientEstimations()
    {
        return $this->hasMany('Workdo\Lead\Entities\Estimation', 'client_id', 'id');
    }

    public function clientContracts()
    {
        return $this->hasMany('Workdo\Lead\Entities\Contract', 'client_name', 'id');
    }

    public static function clientPermission($dealId)
    {
        return ClientPermission::where('client_id', '=', $this->id)->where('deal_id', '=', $dealId)->first();
    }

}
