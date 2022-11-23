<?php
declare(strict_types=1);

namespace Vitaliy914\OneCApi\Parser;

trait XmlModel
{
    /**
     * @var Model
     */
    private $model;

    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $pId;

    /**
     * @var array
     */
    private $fillable;

    /**
     * @param string $model
     */
    private function initModel(string $model) : void
    {
        $this->model = config('one-c.models.'.$model.'.model');
        $this->id = config('one-c.models.'.$model.'.id');
        $this->pId = config('one-c.models.'.$model.'.parent_id');
        $this->fillable = config('one-c.models.'.$model.'.fillable');
    }

    /**
     * @param \SimpleXMLElement $xml
     * @return array
     */
    private function setModel(\SimpleXMLElement $xml) : array
    {
        $data = [];
        foreach ($this->fillable as $key => $val)
        {
            if(isset($xml->{$val})){
                $data[$key] = (string) $xml->{$val};
            }
        }
        return $data;
    }

    /**
     * Можно ли создать класс
     * @return bool
     * @throws \ReflectionException
     */
    private function isInstantiable() : bool
    {
        if(!$this->model)
            return false;
        $reflectionClass = new \ReflectionClass($this->model);
        return $reflectionClass->isInstantiable();
    }
}
