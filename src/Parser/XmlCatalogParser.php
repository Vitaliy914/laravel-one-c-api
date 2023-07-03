<?php
declare(strict_types=1);
namespace Vitaliy914\OneCApi\Parser;

use Vitaliy914\OneCApi\Exception\ExceptionOneCApi;

class XmlCatalogParser extends Xml
{
    private $classifier;

    private $products;

    /**
     * Инициализация
     * @param string $fileName
     * @return XmlCatalogParser
     * @throws ExceptionOneCApi
     */
    public function init(string $fileName) : XmlCatalogParser
    {
        $fullPath = $this->getPath($fileName);

        $this->xml = $this->loadXml($fullPath);

        if(!$this->xml)
            throw new ExceptionOneCApi('OneCApi: Parse error: not found ' . $fullPath);

        $this->setClassifier();

        $this->setProducts();

        return $this;
    }

    /**
     * Выполняем парсинг
     */
    public function runCatalog() : void
    {
        $this->classifier
            ->groups()
            ->properties();
        $this->products->run($this->xml->{'Каталог'}->{'Товары'}->{'Товар'});
        \Log::debug('Import groups completed');
    }

    /**
     * Загружаем классификатор
     */
    private function setClassifier() : void
    {
        $this->classifier = new XmlClassifierParser(
            isset($this->xml->{'Классификатор'})?$this->xml->{'Классификатор'}:null
            );
        \Log::debug('Import Classifier completed');
    }

    /**
     * Товары
     */
    private function setProducts() : void
    {
        $this->products = new XmlProductParser();
        \Log::debug('Import Products completed');
    }
}
