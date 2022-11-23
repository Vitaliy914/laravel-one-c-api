<?php
namespace Vitaliy914\OneCApi\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OnecapiPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'type_sku',
        'view',
        'price_per_unit',
        'currency',
        'unit',
        'ratio',
        'discount',
        'price_with_discount',
    ];
}
