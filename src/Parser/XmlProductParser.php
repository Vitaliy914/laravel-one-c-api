<?php
declare(strict_types=1);
namespace Vitaliy914\OneCApi\Parser;

class XmlProductParser
{
    use XmlModel;
    use XmlObserver;

    private $attributeValuesParse;

    private $propertyValueParser;

    public function __construct()
    {
        $this->initModel('product');
        $this->initObserver('product');

        $this->attributeValuesParse = new XmlAttributeValuesParse();
        $this->propertyValueParser = new XmlPropertyValueParser();
    }

    /**
     * @param \SimpleXMLElement $products
     * @throws \ReflectionException
     */
    public function run(\SimpleXMLElement $products) : void
    {
        foreach ($products as $product) {
            $item = $this->model::where($this->id, $product->{'Ид'})->first();

            // если найден то обновляем только филлабле поля
            if ($item) {
                $item->fill(
                    $this->setModel($product)
                );
                $item->setAttribute($this->pId, (string)$product->{'Группы'}->{'Ид'});

                $this->runObserver('updating', $item, $product);

                $item->update();

                $this->runObserver('updated', $item, $product);

            } else { // если нет, создаем новую запись
                $item = new $this->model();
                $item->setAttribute($this->id, (string)$product->{'Ид'});
                $item->setAttribute($this->pId, (string)$product->{'Группы'}->{'Ид'});
                $item->fill(
                    $this->setModel($product)
                );

                $this->runObserver('creating', $item, $product);

                $item->save();

                $this->runObserver('created', $item, $product);
            }

            // Парсим заруженные изображения
            if(isset($product->{'Картинка'})){
                $imageClass = config('one-c.models.product.images');
                $reflectionClass = new \ReflectionClass($imageClass);
                if($reflectionClass->isInstantiable()) {
                    $image = new $imageClass();
                    if ($image instanceof XmlImageParserInterface)
                        $image->run([$product->{'Картинка'}], $item);
                }
            }

            if(isset($product->{'ЗначенияРеквизитов'}->{'ЗначениеРеквизита'})){
                $this->attributeValuesParse->run(
                    $product->{'ЗначенияРеквизитов'}->{'ЗначениеРеквизита'},
                    (string)$product->{'Ид'}
                    );
            }


            if(isset($product->{'ЗначенияСвойств'}->{'ЗначенияСвойства'})){
                $this->propertyValueParser->run(
                    $product->{'ЗначенияСвойств'}->{'ЗначенияСвойства'},
                    (string)$product->{'Ид'}
                );
            }
        }
        $directory = config('one-c.setup.app_path');
        $cmd = "cp -nR ".$directory."/storage/app/onec/import_files/. ".$directory."storage/app/public/images/";
        exec($cmd);
   }
}
