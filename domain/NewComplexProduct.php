<?php

namespace NewCMS\Domain;

use TRMEngine\DataObject\TRMDataObjectsContainer;
use TRMEngine\DataObject\TRMParentedCollection;
use TRMEngine\EventObserver\Interfaces\TRMEventInterface;
use TRMEngine\EventObserver\TRMCommonEvent;
use TRMEngine\EventObserver\TRMEventManager;

/**
 * класс составного продукта со всеми дополнительными коллекциями и объектами
 * (характеристики, комплект, доп.изображениями и т.д.)
 */
class NewComplexProduct extends TRMDataObjectsContainer
{
/**
 * константа с название собыьия о смене ID главного объетка
 */
const CHANGE_ID_EVENT_NAME = "ProductChangeId";
/**
 * @var NewProduct - основной объект
 */
protected $MainDataObject;
/**
 * @var string - тип главного объекта 
 */
static protected $MainDataObjectType = NewProduct::class; // NewLiteProduct::class;
/**
 * @var TRMEventManager - менеджер событий
 */
protected $EventManager;
/**
 * @var array(\TRMEngine\EventObserver\Interfaces\TRMEventInterface) - массив событий, 
 * которые может генерировать этот контейнер
 */
protected $Events = array();


public function __construct(TRMEventManager $EM)
{
    $this->MainDataObject = new NewProduct();

    $this->EventManager = $EM;

    $this->setChildCollection( 
        "ProductFeaturesCollection", 
        new NewProductFeaturesCollection($this, $this->EventManager) 
    );
    $this->setChildCollection( 
        "ProductMetaTagsCollection", 
        new NewProductMetaTagsCollection($this, $this->EventManager) 
    );
    $this->setChildCollection( 
        "ComplectCollection", 
        new NewComplectCollection($this, $this->EventManager) 
    );
    $this->setChildCollection( 
        "ImagesCollection", 
        new NewImagesCollection($this, $this->EventManager) 
    );
    $this->setChildCollection( 
        "FilesCollection", 
        new NewFilesCollection($this, $this->EventManager) 
    );

    // создаем событие, которое будет рассылаться при изменении ID
    $this->Events[self::CHANGE_ID_EVENT_NAME] = 
            new TRMCommonEvent($this, self::CHANGE_ID_EVENT_NAME);
}


public function resetId()
{
    parent::resetId();
    $this->EventManager->notifyObservers($this->Events[self::CHANGE_ID_EVENT_NAME]);
}

public function setId($id)
{
    parent::setId($id);
    $this->EventManager->notifyObservers($this->Events[self::CHANGE_ID_EVENT_NAME]);
}

/**
 * @return NewLiteProduct
 */
function getLiteProduct()
{
    return $this->MainDataObject->getMainDataObject();
}

/**
 * @param NewLiteProduct $LiteProduct
 */
function setLiteProduct(NewLiteProduct $LiteProduct)
{
    $this->MainDataObject->setMainDataObject($LiteProduct);
    $this->EventManager->notifyObservers($this->Events[self::CHANGE_ID_EVENT_NAME]);
}

/**
 * устанавливает базовую (начальную) цену товара и вычисляет 3 цены с наценками
 * 
 * @param double $Price0 - начальная цена в валюте товара
 */
public function setPrice0($Price0)
{
     $this->MainDataObject->setPrice0($Price0);
}


} // NewComplexProduct


/**
 * абстрактный класс для коллекций, которые подписываются на событие изменеия ID
 * у родительского объекта-контейнера - NewComplexProduct::CHANGE_ID_EVENT_NAME
 */
abstract class NewParentIdEventedCollection extends TRMParentedCollection
{
/**
 * @var TRMEventManager - менеджер событий
 */
protected $EventManager;

/**
 * @param string $ObjectsType - имя класса (тип) объектов, хранимых в коллекции
 * @param NewComplexProduct $ParentDataObject - указатель на объект-родителя для коллекции
 * @param TRMEventManager $EventManager - менеджер событий в системе
 */
public function __construct($ObjectsType, NewComplexProduct $ParentDataObject, TRMEventManager $EventManager)
{
    parent::__construct($ObjectsType, $ParentDataObject);
    $this->EventManager = $EventManager;
    $this->EventManager->addObserver($this, NewComplexProduct::CHANGE_ID_EVENT_NAME, "handleChangeParentId");
}

public function handleChangeParentId(TRMEventInterface $e)
{
    // если событие отправлено родителем данной коллекции,
    // то запускается обработчик
    if($e->getSender() === $this->ParentDataObject)
    {
        // при установке родителя для коллекции,
        // все ее объекты переустанавливают ID-родителя
        // поэтому просто вызывается setParentDataObject со старым родителем
        $this->setParentDataObject($this->ParentDataObject);
    }
}


} // NewParentIdEventedCollection

class NewProductFeaturesCollection extends NewParentIdEventedCollection
{
/**
 * @var array - массив = (имя объекта, имя свойства) содержащего Id родителя в коллекции,
 * должен определяться в каждом дочернем классе со своими именами
 */
static protected $ParentIdFieldName;
/**
 * @var TRMEventManager - менеджер событий
 */
protected $EventManager;

public function __construct(NewComplexProduct $ParentDataObject, TRMEventManager $EventManager)
{
    parent::__construct(NewProductFeature::class, $ParentDataObject, $EventManager);
}


} // NewProductFeaturesCollection


class NewProductMetaTagsCollection extends NewParentIdEventedCollection
{
/**
 * @var array - массив = (имя объекта, имя свойства) содержащего Id родителя в коллекции,
 * должен определяться в каждом дочернем классе со своими именами
 */
static protected $ParentIdFieldName;
/**
 * @var TRMEventManager - менеджер событий
 */
protected $EventManager;

public function __construct(NewComplexProduct $ParentDataObject, TRMEventManager $EventManager)
{
    parent::__construct(NewProductMetaTag::class, $ParentDataObject, $EventManager);
}


} // NewProductMetaTagsCollection


class NewComplectCollection extends NewParentIdEventedCollection
{
/**
 * @var array - массив = (имя объекта, имя свойства) содержащего Id родителя в коллекции,
 * должен определяться в каждом дочернем классе со своими именами
 */
static protected $ParentIdFieldName;
/**
 * @var TRMEventManager - менеджер событий
 */
protected $EventManager;

public function __construct(NewComplexProduct $ParentDataObject, TRMEventManager $EventManager)
{
    parent::__construct(NewComplectPart::class, $ParentDataObject, $EventManager);
}


} // NewProductFeaturesCollection


class NewImagesCollection extends NewParentIdEventedCollection
{
/**
 * @var array - массив = (имя объекта, имя свойства) содержащего Id родителя в коллекции,
 * должен определяться в каждом дочернем классе со своими именами
 */
static protected $ParentIdFieldName;
/**
 * @var TRMEventManager - менеджер событий
 */
protected $EventManager;

public function __construct(NewComplexProduct $ParentDataObject, TRMEventManager $EventManager)
{
    parent::__construct(NewProductImage::class, $ParentDataObject, $EventManager);
}


} // NewProductFeaturesCollection


class NewFilesCollection extends NewParentIdEventedCollection
{
/**
 * @var array - массив = (имя объекта, имя свойства) содержащего Id родителя в коллекции,
 * должен определяться в каждом дочернем классе со своими именами
 */
static protected $ParentIdFieldName;
/**
 * @var TRMEventManager - менеджер событий
 */
protected $EventManager;

public function __construct(NewComplexProduct $ParentDataObject, TRMEventManager $EventManager)
{
    parent::__construct(NewProductFile::class, $ParentDataObject, $EventManager);
}


} // NewProductFeaturesCollection
