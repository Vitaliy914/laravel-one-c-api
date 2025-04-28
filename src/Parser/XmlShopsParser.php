<?php
declare(strict_types=1);

namespace Vitaliy914\OneCApi\Parser;


class XmlShopsParser
{
    use XmlModel;
    use XmlObserver;


    public function __construct()
    {
        $this->initModel('shops');
    }

    /**
     * @param \SimpleXMLElement $shops
     * @throws \ReflectionException
     */
    public function run(\SimpleXMLElement $shops) : void
    {
        \Log::debug('shops start');
        foreach ($shops as $shop) {
            $shopId = explode('#', (string)$shop->{'Ид'});
            foreach ($shopId as $id) {
                $item = $this->model::where('shop_sku', $id)->first();
                if ($item) {
                    $item->fill(
                        $this->setModel($shop)
                    );
                    $item->update();
                } else { // если нет, создаем новую запись
                    $item = new $this->model();
                    $item->setAttribute($this->id, $id);
                    $item->fill(
                        $this->setModel($shop)
                    );
                    $item->save();
                }
            }
        }
    }
}
