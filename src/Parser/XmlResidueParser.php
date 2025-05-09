<?php
declare(strict_types=1);

namespace Vitaliy914\OneCApi\Parser;


class XmlResidueParser
{
    use XmlModel;
    use XmlObserver;

    private $priceParser;
    private $leftoversParser;

    public function __construct()
    {
        $this->initModel('residue');
        $this->initObserver('residue');

        $this->priceParser = new XmlPriceParser();
        $this->leftoversParser = new XmlLeftoversParser();
    }

    /**
     * @param \SimpleXMLElement $products
     * @throws \ReflectionException
     */
    public function run(\SimpleXMLElement $products) : void
    {
        foreach ($products as $product) {
            $productId = explode('#', (string)$product->{'Ид'});

            foreach ($productId as $id) {
                $item = $this->model::where($this->id, $id)->first();

                // если найден то обновляем только филлабле поля
                if ($item) {
                    if (isset($product->{'Цены'}->{'Цена'}))
                        $this->priceParser->run($product->{'Цены'}->{'Цена'}, $id);
                    if (isset($product->{'Магазины'}->{'Магазин'}))
                        $this->leftoversParser->run($product->{'Магазины'}->{'Магазин'}, $id);

                }
            }
        }
    }
}
