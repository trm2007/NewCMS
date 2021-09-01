<?php

namespace NewCMS\Domain;

use TRMEngine\DataObject\TRMParentedDataObject;

/**
 * класс для коллекций MetaTeg-ов товара
 */
class NewProductMetaTag extends TRMParentedDataObject
{
/**
 * @var array - массив = (имя объекта, имя свойства) содержащего Id родителя в коллекции,
 * должен определяться в каждом дочернем классе со своими именами
 */
static protected $ParentIdFieldName = array( "new_products_metatags", "ID_Price" );


} // NewProductMetaTag
