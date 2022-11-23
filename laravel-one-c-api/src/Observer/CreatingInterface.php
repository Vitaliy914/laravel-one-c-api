<?php

namespace Vitaliy914\OneCApi\Observer;


use Illuminate\Database\Eloquent\Model;

interface CreatingInterface
{
    // Событие до сохранения в бд данных
    public function creating(Model $model, \SimpleXMLElement $xml) : void;
}
