<?php
declare(strict_types=1);
namespace Vitaliy914\OneCApi\Parser;

use Vitaliy914\OneCApi\Exception\ExceptionOneCApi;

class XmlClassifierParser extends Xml
{
    public function __construct(\SimpleXMLElement $classifier = null)
    {
        $this->init($classifier);
    }

    /**
     * @param \SimpleXMLElement|null $classifier
     * @return XmlClassifierParser
     * @throws ExceptionOneCApi
     */
    public function init(\SimpleXMLElement $classifier = null) : XmlClassifierParser
    {
        if($classifier){
            $this->xml = $classifier;
        }
        else{
            $fullPath = $this->getPath('classifier.xml');
            $this->xml = $this->loadXml($fullPath);
            if(!$this->xml)
                throw new ExceptionOneCApi('OneCApi: Parse error: not found ' . $fullPath);
        }
        return $this;
    }

    /**
     * Парсим группы
     * @return XmlClassifierParser
     * @throws ExceptionOneCApi
     */
    public function groups() : XmlClassifierParser
    {
        if(isset($this->xml->{'Группы'}->{'Группа'})){
            $groupParser = new XmlGroupParser();
            $groupParser->run($this->xml->{'Группы'}->{'Группа'});
        }
        else
            throw new ExceptionOneCApi('OneCApi: Parse error: group not found.');

        return $this;
    }

    /**
     * Парсим пропертисы
     * @return XmlClassifierParser
     */
    public function properties() : XmlClassifierParser
    {
        if(isset($this->xml->{'Свойства'}->{'Свойство'})){
            $propertyParser = new XmlPropertyParser();
            $propertyParser->run($this->xml->{'Свойства'}->{'Свойство'});
        }

        return $this;
    }
}
