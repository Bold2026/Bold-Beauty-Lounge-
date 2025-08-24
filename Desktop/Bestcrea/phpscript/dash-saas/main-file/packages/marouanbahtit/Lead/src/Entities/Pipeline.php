<?php

namespace Workdo\Lead\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pipeline extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'created_by',
        'workspace_id',
    ];

    public function dealStages()
    {
        return $this->hasMany('Workdo\Lead\Entities\DealStage', 'pipeline_id', 'id')->where('created_by', '=', creatorId())->orderBy('order');
    }

    public function leadStages()
    {
        return $this->hasMany('Workdo\Lead\Entities\LeadStage', 'pipeline_id', 'id')->where('created_by', '=', creatorId())->orderBy('order');
    }
}
