<?php

namespace NewCMS\Domain;

use TRMEngine\DataObject\TRMIdDataObject;

/**
 * информацмя о заказе, без списка товаров
 *
 * @date 2019-05-02
 */
class NewOrder extends TRMIdDataObject
{
/**
 * @var array - имя свойства для идентификатора объекта, обычно совпадает с именем ID-поля из БД
 */
static protected $IdFieldName = array( "new_orders", "id" );


} // NewOrder
