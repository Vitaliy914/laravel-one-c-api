<?php
declare(strict_types=1);

namespace Vitaliy914\OneCApi\Parser;


class XmlPropertyValueParser
{
    use XmlModel;

    public function __construct()
    {
        $this->initModel('property_values');
    }

    /**
     * @param \SimpleXMLElement $values
     * @param string $productId
     * @throws \ReflectionException
     */
    public function run(\SimpleXMLElement $values, string $productId) : void
    {
        // если класс не определен то не парсим просто выходим без ошибок
        if(!$this->isInstantiable()) {
            return;
        }

        // Удаляем старые
        $this->model::where($this->id, $productId)->delete();

        foreach ($values as $value) {
            $item = new $this->model();
            $item->setAttribute($this->id, $productId);
            $item->fill(
                $this->setModel($value)
            );
            $item->save();
        }
    }
}
