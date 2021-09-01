<?php

namespace NewCMS\Domain;

use TRMEngine\DataObject\TRMIdDataObject;

/**
 * класс новости
 */
class NewNews extends TRMIdDataObject
{
/**
 * @var array - имя объекта и свойства для идентификатора новости, 
 * обычно совпадают с именем таблицы и ID-поля из БД
 */
static protected $IdFieldName = array( "news", "ID_new" );


} // NewNews
