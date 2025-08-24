<?php

namespace Workdo\Lead\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Label extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'color',
        'pipeline_id',
        'workspace_id',
        'created_by',
    ];

    public static $colors = [
        'primary',
        'secondary',
        'danger',
        'warning',
        'info',
    ];

    public static $colorCode = [
        'primary'   => '#3B71CA',
        'secondary' => '#9FA6B2',
        'danger'    => '#DC4C64',
        'warning'   => '#E4A11B',
        'info'      => '#54B4D3',
    ];
}
