<?php
namespace Vitaliy914\OneCApi\Parser;


use Illuminate\Database\Eloquent\Model;

interface XmlImageParserInterface
{
    public function run(array $images, Model $model) : void;
}
