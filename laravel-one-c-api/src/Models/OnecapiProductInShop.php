<?php
namespace Vitaliy914\OneCApi\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OnecapiProductInShop extends Model
{
    use HasFactory;
    protected $table = 'onecapi_products_in_shops';
    protected $fillable = [
        'shop_sku',
        'product_sku',
        'count'
    ];
}
