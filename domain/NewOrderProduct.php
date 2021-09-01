<?php

namespace NewCMS\Domain;

use TRMEngine\DataObject\TRMParentedDataObject;

/**
 * с 2019.05.02 - класс для работы с товаром для заказа
 *
 * @author TRM
 */
class NewOrderProduct extends TRMParentedDataObject
{
/**
 * @var array - массив = (имя объекта, имя свойства) содержащего Id родителя в коллекции,
 * должен определяться в каждом дочернем классе со своими именами
 */
static protected $ParentIdFieldName = array( "new_order_products", "id_order" );

/**
 * устанавливает данные товара из объекта NewLiteProductForCollection
 * 
 * @param NewLiteProductForCollection $LiteProduct
 */
public function initFromLiteProduct(NewLiteProductForCollection $LiteProduct)
{
    $this->setRow("table1", $LiteProduct->getRow("table1") );
    $this->setData("new_order_products", "id_product", $LiteProduct->getData("table1", "ID_price"));
}

public function setCount($Count)
{
    $this->setData("new_order_products", "count", $Count);
}


} // NewOrderProduct