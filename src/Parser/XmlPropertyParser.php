<?php
declare(strict_types=1);

namespace Vitaliy914\OneCApi\Parser;


class XmlPropertyParser
{
    use XmlModel;
    use XmlObserver;

    private $propertyVariant;

    public function __construct()
    {
        $this->initModel('property');
        $this->initObserver('property');

        $this->propertyVariant = new XmlPropertyVariantParser();
    }

    /**
     * @param \SimpleXMLElement $properties
     */
    public function run(\SimpleXMLElement $properties) : void
    {
        foreach ($properties as $property) {
            $item = $this->model::where($this->id, $property->{'Ид'})->first();

            // если найден то обновляем только филлабле поля
            if ($item) {
                $item->fill(
                    $this->setModel($property)
                );

                $this->runObserver('updating', $item, $property);

                $item->update();

                $this->runObserver('updated', $item, $property);

            } else { // если нет, создаем новую запись
                $item = new $this->model();
                $item->setAttribute($this->id, (string)$property->{'Ид'});
                $item->fill(
                    $this->setModel($property)
                );

                $this->runObserver('creating', $item, $property);

                $item->save();

                $this->runObserver('created', $item, $property);
            }

            if(isset($property->{'ВариантыЗначений'}->{'Справочник'}))
                $this->propertyVariant->run($property->{'ВариантыЗначений'}->{'Справочник'}, (string)$property->{'Ид'});
        }
    }
}
