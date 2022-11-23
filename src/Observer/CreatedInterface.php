<?php
namespace Vitaliy914\OneCApi\Observer;

use Illuminate\Database\Eloquent\Model;

interface CreatedInterface
{
    // Событие после создания и сохраннения новой модели
    public function created(Model $model, \SimpleXMLElement $xml) : void;
}
