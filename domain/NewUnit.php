<?php

namespace NewCMS\Domain;

use TRMEngine\DataObject\TRMIdDataObject;

/**
 * класс производителя товаров
 */
class NewUnit extends TRMIdDataObject
{
/**
 * @var array - имя свойства для идентификатора объекта, обычно совпадает с именем ID-поля из БД
 */
static protected $IdFieldName = array( "unit", "ID_unit" );


} // NewUnit
