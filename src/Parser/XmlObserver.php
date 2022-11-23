<?php
declare(strict_types=1);
namespace Vitaliy914\OneCApi\Parser;

use Illuminate\Database\Eloquent\Model;
use Vitaliy914\OneCApi\Observer\CreatedInterface;
use Vitaliy914\OneCApi\Observer\CreatingInterface;
use Vitaliy914\OneCApi\Observer\UpdatedInterface;
use Vitaliy914\OneCApi\Observer\UpdatingInterface;

trait XmlObserver
{
    private $observer;

    /**
     * События и интерфейсы
     * @var array
     */
    private $events = [
        'created'  => CreatedInterface::class,
        'creating' => CreatingInterface::class,
        'updated'  => UpdatedInterface::class,
        'updating' => UpdatingInterface::class
    ];

    /**
     * @param string $model
     * @throws \ReflectionException
     */
    private function initObserver(string $model) : void
    {
        $this->observer = null;
        $observer = config('one-c.models.'.$model.'.observer');

        if($observer) {
            $reflectionClass = new \ReflectionClass($observer);

            if (!$reflectionClass->isInstantiable()) {
                $this->observer = null;
            } else {
                $this->observer = new $observer();
            }
        }
    }

    /**
     * @param string $event
     * @return string|null
     */
    private function observerGetInterface(string $event) : ?string
    {
        if(isset($this->events[$event])){
            return $this->events[$event];
        }

        return null;
    }

    /**
     * Попробовать выполнить событие
     *
     * @param string $event
     * @param Model $model
     * @param \SimpleXMLElement $xml
     */
    private function runObserver(string $event, Model $model, \SimpleXMLElement $xml)
    {
        $eventInterface = $this->observerGetInterface($event);
        if($this->observer && $eventInterface){
            if($this->observer instanceof $eventInterface){
                $this->observer->$event($model, $xml);
            }
        }
    }
}
