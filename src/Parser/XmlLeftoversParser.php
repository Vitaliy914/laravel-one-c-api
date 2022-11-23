<?php
declare(strict_types=1);

namespace Vitaliy914\OneCApi\Parser;


class XmlLeftoversParser
{
    use XmlModel;
    use XmlObserver;

    public function __construct()
    {
        $this->initModel('leftovers');

        \Log::debug('leftovers init');
    }

    /**
     * @param \SimpleXMLElement $leftovers
     * @param string $productId
     * @throws \ReflectionException
     */
    public function run(\SimpleXMLElement $leftovers, string $productId) : void
    {
        \Log::debug('leftovers start');
        // если класс не определен то не парсим просто выходим без ошибок
        if(!$this->isInstantiable()) {
            return;
        }

        // Удаляем старые
        $this->model::where($this->id, $productId)->delete();

        foreach ($leftovers as $leftover) {
            $item = new $this->model();
            $item->setAttribute($this->id, $productId);
            $item->fill(
                $this->setModel($leftover)
            );
            $item->save();
        }
    }
}
