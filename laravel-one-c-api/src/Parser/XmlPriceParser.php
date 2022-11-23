<?php
declare(strict_types=1);

namespace Vitaliy914\OneCApi\Parser;


class XmlPriceParser
{
    use XmlModel;
    use XmlObserver;

    public function __construct()
    {
        $this->initModel('prices');
        $this->initObserver('prices');
    }

    /**
     * @param \SimpleXMLElement $prices
     * @param string $productId
     * @throws \ReflectionException
     */
    public function run(\SimpleXMLElement $prices, string $productId) : void
    {
        // если класс не определен то не парсим просто выходим без ошибок
        if(!$this->isInstantiable()) {
            return;
        }

        // Удаляем старые
        $this->model::where($this->id, $productId)->delete();

        foreach ($prices as $price) {
            $item = new $this->model();
            $item->setAttribute($this->id, $productId);
            $item->fill(
                $this->setModel($price)
            );
            $item->save();
        }
    }
}
