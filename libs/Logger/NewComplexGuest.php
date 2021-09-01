<?php

namespace NewCMS\Libs\Logger;

use NewCMS\Libs\Logger\NewGuestVisited;
use TRMEngine\DataObject\Interfaces\TRMIdDataObjectInterface;
use TRMEngine\DataObject\TRMDataObjectsContainer;
use TRMEngine\DataObject\TRMParentedCollection;

/**
 * класс для работы с посетителем сайта и коллекцией объектов посещенных 
 * @date 2019-05-27
 */
class NewComplexGuest extends TRMDataObjectsContainer
{
/**
 * @var string - тип главного объекта 
 */
static protected $MainDataObjectType = NewGuest::class;

public function __construct()
{
    $this->MainDataObject = new NewGuest();
    $this->setChildCollection( "GuestVisitedCollection", new NewGuestVisitedCollection($this) );
}

/**
 * 
 * @return NewGuest - возвращает простой объект гость (посетитель сайта), 
 * являющийся главным для NewComplexGuest
 */
public function getGuest()
{
    return $this->MainDataObject;
}


} // NewComplexGuest


class NewGuestVisitedCollection extends TRMParentedCollection
{
/**
 * @var array - массив = (имя объекта, имя свойства) содержащего Id родителя в коллекции,
 * должен определяться в каждом дочернем классе со своими именами
 */
static protected $ParentIdFieldName;

public function __construct(TRMIdDataObjectInterface $ParentDataObject)
{
    parent::__construct(NewGuestVisited::class, $ParentDataObject);
}


} // NewGuestVisitedCollection
