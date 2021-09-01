<?php

namespace NewCMS\Domain;

use NewCMS\Libs\NewBasket;
use TRMEngine\DataObject\Interfaces\TRMIdDataObjectInterface;
use TRMEngine\DataObject\TRMDataObjectsContainer;
use TRMEngine\DataObject\TRMParentedCollection;

/**
 * класс для работы с заказом, содержть дочернюю коллекцию товаров заказа
 * 2018-06-30
 *
 * @author TRM
 */
class NewComplexOrder extends TRMDataObjectsContainer
{
/**
 * @var string - тип главного объекта 
 */
static protected $MainDataObjectType = NewOrder::class;

public function __construct()
{
    $this->MainDataObject = new NewOrder();
    $this->setChildCollection( "OrderProductsCollection", new NewOrderProductsCollection($this) );
}

/**
 * инициализирует коллекцию товаров заказа из корзины
 * 
 * @param NewBasket $Basket
 */
public function initFromBasket(NewBasket $Basket)
{
    $this->getChildCollection("OrderProductsCollection")->clearCollection();
    foreach( $Basket->Goods as $Product )
    {
        $OrderProduct = new NewOrderProduct();
        $OrderProduct->initFromLiteProduct( $Product->Item );
        $OrderProduct->setCount($Product->Count);
        
        $this->getChildCollection("OrderProductsCollection")->addDataObject($OrderProduct);
    }
}

public function setFIO($FIO)
{
    $this->MainDataObject->setData("new_orders", "fio", $FIO);
}
public function setEmail($Email)
{
    $this->MainDataObject->setData("new_orders", "email", $Email );
}
public function setPhone($Phone)
{
    $this->MainDataObject->setData("new_orders", "phone", $Phone );
}
public function setMessage($Message)
{
    $this->MainDataObject->setData("new_orders", "message", $Message );
}
public function setDateFromTime($Time)
{
    $this->MainDataObject->setData("new_orders", "date", date("Y-m-d H:i:s", $Time) );
}
public function setSessionId($SessionId)
{
    $this->MainDataObject->setData("new_orders", "session_id", $SessionId );
}

} // NewComplexOrder

class NewOrderProductsCollection extends TRMParentedCollection
{
/**
 * @var array - массив = (имя объекта, имя свойства) содержащего Id родителя в коллекции,
 * должен определяться в каждом дочернем классе со своими именами
 */
static protected $ParentIdFieldName;

public function __construct(TRMIdDataObjectInterface $ParentDataObject)
{
    parent::__construct(NewOrderProduct::class, $ParentDataObject);
}


} // NewOrderProductsCollection
