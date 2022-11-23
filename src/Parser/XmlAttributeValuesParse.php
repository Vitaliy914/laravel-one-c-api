<?php
declare(strict_types=1);

namespace Vitaliy914\OneCApi\Parser;

class XmlAttributeValuesParse
{
    use XmlModel;

    public function __construct()
    {
        $this->initModel('attribute_values');
    }

    /**
     * @param \SimpleXMLElement $attributes
     * @param string $productId
     * @throws \ReflectionException
     */
    public function run(\SimpleXMLElement $attributes, string $productId) : void
    {
        // если класс не определен то не парсим просто выходим без ошибок
        if(!$this->isInstantiable()) {
            return;
        }

        // Удаляем старые
        $this->model::where($this->id, $productId)->delete();

        foreach ($attributes as $attribute) {
            $item = new $this->model();
            $item->setAttribute($this->id, $productId);
            $item->fill(
                $this->setModel($attribute)
            );
            $item->save();
        }
    }
}
