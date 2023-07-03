<?php
declare(strict_types=1);
namespace Vitaliy914\OneCApi\Parser;

use Vitaliy914\OneCApi\Exception\ExceptionOneCApi;

class XmlOffersParser extends Xml
{
    private $priceTypeParser;

    private $residueParser;

    private $shopsParser;

    /**
     * @param string $fileName
     * @return XmlOffersParser
     * @throws ExceptionOneCApi
     */
    public function init(string $fileName) : XmlOffersParser
    {
        $fullPath = $this->getPath($fileName);
        $this->xml = $this->loadXml($fullPath);

        if(!$this->xml)
            throw new ExceptionOneCApi('OneCApi: Parse error: not found ' . $fullPath);

        $this->priceTypeParser = new XmlPriceTypeParser();
        $this->residueParser = new XmlResidueParser();
        $this->shopsParser = new XmlShopsParser();

        return $this;
    }

    /**
     * Выполнить
     */
    public function run()
    {
        $this->priceType()
            ->offer()
            ->shops();
    }

    /**
     * Парсим типы цен
     * @return XmlOffersParser
     */
    public function priceType() : XmlOffersParser
    {
        $this->priceTypeParser->run($this->xml->{'ПакетПредложений'}->{'ТипыЦен'}->{'ТипЦены'});
        \Log::debug('Import priceType completed');
        return $this;
    }

    /**
     * Парсим магазины
     * @return XmlOffersParser
     */
    public function shops() : XmlOffersParser
    {
        if(isset($this->xml->{'ПакетПредложений'}->{'Магазины'}->{'Магазин'}))
            $this->shopsParser->run($this->xml->{'ПакетПредложений'}->{'Магазины'}->{'Магазин'});
        \Log::debug('Import shops completed');
        return $this;
    }

    /**
     * Парсим остатки и цены
     * @return XmlOffersParser
     */
    public function offer() : XmlOffersParser
    {
        $this->residueParser->run($this->xml->{'ПакетПредложений'}->{'Предложения'}->{'Предложение'});
        \Log::debug('Import  offer completed');
        return $this;
    }
}
