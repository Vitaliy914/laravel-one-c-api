<?php

namespace Vitaliy914\OneCApi\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OnecapiProperty extends Model
{
    use HasFactory;

    protected $fillable = [
        'name'
    ];

    public function variants()
    {
        return $this->hasMany('\Vitaliy914\OneCApi\Models\OnecapiPropertyVariant', 'property_sku', 'sku');
    }
}
