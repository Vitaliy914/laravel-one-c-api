<?php
declare(strict_types=1);
namespace Vitaliy914\OneCApi\Parser;

use Vitaliy914\OneCApi\Exception\ExceptionOneCApi;


abstract class Xml
{
    protected $xml;

    /**
     * @param string $fileName
     * @return \SimpleXMLElement|null
     */
    protected function loadXml(string $fileName) : ?\SimpleXMLElement
    {
        if (is_file($fileName)) {
            return simplexml_load_string(file_get_contents($fileName));
        }
        return null;
    }

    /**
     * @param string $fileName
     * @return string
     */
    protected function getPath(string $fileName) : string
    {
        return config('one-c.setup.import_dir') . DIRECTORY_SEPARATOR . $fileName;
    }
}
