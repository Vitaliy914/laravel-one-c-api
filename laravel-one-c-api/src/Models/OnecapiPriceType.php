<?php
namespace Vitaliy914\OneCApi\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OnecapiPriceType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'currency'
    ];
}
