<?php

namespace NewCMS\Domain;

use TRMEngine\DataObject\Interfaces\TRMIdDataObjectInterface;
use TRMEngine\DataObject\TRMDataObjectsContainer;
use TRMEngine\DataObject\TRMParentedCollection;

/**
 * класс составного продукта со всеми дополнительными коллекциями и объектами
 * (характеристики, комплект, доп.изображениями и т.д.)
 */
class NewComplexGroup extends TRMDataObjectsContainer
{
/**
 * @var NewProduct - основной объект
 */
protected $MainDataObject;
/**
 * @var string - тип главного объекта 
 */
static protected $MainDataObjectType = NewGroup::class; // NewLiteProduct::class;

public function __construct()
{
    $this->MainDataObject = new NewGroup();

    $this->setChildCollection( 
        "GroupFeaturesCollection", 
        new NewGroupFeaturesCollection($this) 
    );
}

/**
 * @return NewGroup
 */
function getGroup()
{
    return $this->MainDataObject;
}

/**
 * @param NewGroup $Group
 */
function setGroup(NewGroup $Group)
{
    $this->MainDataObject->setMainDataObject($Group);
}


} // NewComplexProduct

class NewGroupFeaturesCollection extends TRMParentedCollection
{
/**
 * @var array - массив = (имя объекта, имя свойства) содержащего Id родителя в коллекции,
 * должен определяться в каждом дочернем классе со своими именами
 */
static protected $ParentIdFieldName;

public function __construct(TRMIdDataObjectInterface $ParentDataObject)
{
    parent::__construct(NewGroupFeature::class, $ParentDataObject);
}


} // NewGroupFeaturesCollection
