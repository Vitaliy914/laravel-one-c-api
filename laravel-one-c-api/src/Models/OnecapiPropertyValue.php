<?php

namespace Vitaliy914\OneCApi\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OnecapiPropertyValue extends Model
{
    use HasFactory;

    protected $fillable =[
        'property_sku',
        'property_variant_sku'
    ];
}
