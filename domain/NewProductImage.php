<?php

namespace NewCMS\Domain;

use TRMEngine\DataObject\TRMParentedDataObject;

/**
 * с 2018.07.23 - класс для работы с коллекцией дополнительных изображений
 *
 * @author TRM
 */
class NewProductImage extends TRMParentedDataObject
{
/**
 * @var array - массив = (имя объекта, имя свойства) содержащего Id родителя в коллекции,
 * должен определяться в каждом дочернем классе со своими именами
 */
static protected $ParentIdFieldName = array( "images", "id_good" );


} // NewImagesCollection