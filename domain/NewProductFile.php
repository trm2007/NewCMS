<?php

namespace NewCMS\Domain;

use TRMEngine\DataObject\TRMParentedDataObject;

/**
 * 2019.11.26 - класс для работы с коллекцией файлов для товара
 *
 * @author TRM
 */
class NewProductFile extends TRMParentedDataObject
{
/**
 * @var array - массив = (имя объекта, имя свойства) содержащего Id родителя в коллекции,
 * должен определяться в каждом дочернем классе со своими именами
 */
static protected $ParentIdFieldName = array(  "new_products_files", "ID_Product" );


} // NewProductFile