<?php
declare(strict_types=1);
namespace Vitaliy914\OneCApi\Parser;

use App\Helpers\StringHelper;

class XmlGroupParser
{
    use XmlModel;
    use XmlObserver;

    public function __construct()
    {
        $this->initModel('group');
        $this->initObserver('group');
    }

    /**
     * Парсинг груп
     * @param \SimpleXMLElement $groups
     */
    public function run(\SimpleXMLElement $groups) : void
    {
        foreach ($groups as $group) {
            $item = $this->model::where($this->id, $group->{'Ид'})->first();

            // если найден то обновляем только филлабле поля
            if ($item) {
                $item->fill(
                    $this->setModel($group)
                );
                $item->slug = StringHelper::translitUrl((string)$group->{'Наименование'});
                $item->setAttribute($this->pId, '');

                $this->runObserver('updating', $item, $group);

                $item->update();

                $this->runObserver('updated', $item, $group);

            } else { // если нет, создаем новую запись
                $item = new $this->model();
                $item->setAttribute($this->id, (string)$group->{'Ид'});
                $item->fill(
                    $this->setModel( $group)
                );
                $item->slug = StringHelper::translitUrl((string)$group->{'Наименование'});
                $this->runObserver('creating', $item, $group);

                $item->save();

                $this->runObserver('created', $item, $group);
            }

            if(isset($group->{'Группы'}->{'Группа'}))
                $this->parentGroups((string)$group->{'Ид'}, $group->{'Группы'}->{'Группа'});
        }
    }

    /**
     * @param string $parentId
     * @param \SimpleXMLElement $groups
     */
    private function parentGroups(string $parentId, \SimpleXMLElement $groups) : void
    {
        foreach ($groups as $group) {
            $item = $this->model::where($this->id, $group->{'Ид'})->first();

            if ($item) {
                $item->fill(
                    $this->setModel($group)
                );
                if($item[$this->pId] != $parentId)
                    $item->setAttribute($this->pId, $parentId);

                $this->runObserver('updating', $item, $group);

                $item->update();

                $this->runObserver('updated', $item, $group);
            } else {
                $item = new $this->model;
                $item->setAttribute($this->id, (string)$group->{'Ид'});
                $item->setAttribute($this->pId, $parentId);
                $item->fill(
                    $this->setModel($group)
                );
                $this->runObserver('creating', $item, $group);

                $item->save();

                $this->runObserver('created', $item, $group);
            }

            if(isset($group->{'Группы'}->{'Группа'}))
                $this->parentGroups((string)$group->{'Ид'}, $group->{'Группы'}->{'Группа'});
        }
    }
}
