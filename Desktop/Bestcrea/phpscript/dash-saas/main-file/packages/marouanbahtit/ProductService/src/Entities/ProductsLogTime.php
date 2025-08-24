<?php

namespace Workdo\ProductService\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductsLogTime extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'user_id',
        'hours',
        'minute',
        'date',
        'description',
        'location_id',
        'created_by',
        'company_id',
        'workspace',
    ];

    protected static function newFactory()
    {
        return \Workdo\ProductService\Database\factories\ProductsLogTimeFactory::new();
    }
}
