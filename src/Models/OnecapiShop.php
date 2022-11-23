<?php
namespace Vitaliy914\OneCApi\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OnecapiShop extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_sku',
        'name',
        'short_name'
    ];
}
