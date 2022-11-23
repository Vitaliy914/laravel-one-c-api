<?php

namespace Vitaliy914\OneCApi\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OnecapiProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'art',
        'barcode',
        'residue',
	'description'
    ];

    public function attribute_value()
    {
        return $this->hasMany('\Vitaliy914\OneCApi\Models\OnecapiAttributeValue', 'sku', 'sku');
    }
}
