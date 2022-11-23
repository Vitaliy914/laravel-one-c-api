<?php
declare(strict_types=1);

namespace Vitaliy914\OneCApi\Parser;


class XmlPriceTypeParser
{
    use XmlModel;
    use XmlObserver;

    public function __construct()
    {
        \Log::debug('XmlPriceTypeParser construct');
        $this->initModel('price_type');
        $this->initObserver('price_type');
    }

    /**
     * @param \SimpleXMLElement $priceTypes
     */
    public function run(\SimpleXMLElement $priceTypes) : void
    {
        \Log::debug('XmlPriceTypeParser run');
        foreach ($priceTypes as $type) {
            $item = $this->model::where($this->id, $type->{'Ид'})->first();

            // если найден то обновляем только филлабле поля
            if ($item) {
                $item->fill(
                    $this->setModel($type)
                );
                $this->runObserver('updating', $item, $type);

                $item->update();

                $this->runObserver('updated', $item, $type);

            } else { // если нет, создаем новую запись
                $item = new $this->model();
                $item->setAttribute($this->id, (string)$type->{'Ид'});
                $item->fill(
                    $this->setModel($type)
                );

                $this->runObserver('creating', $item, $type);

                $item->save();

                $this->runObserver('created', $item, $type);
            }
        }
    }
}
