<?php

namespace NewCMS\MapData;

use TRMEngine\DataObject\TRMIdDataObject;

/**
 * класс для объектов данных выбранных на основе заданных таблиц и их полей
 */
class NewMapDataObject extends TRMIdDataObject
{
/**
 * @var array - имя объекта и свойства для идентификатора новости, 
 * обычно совпадают с именем таблицы и ID-поля из БД
 */
static protected $IdFieldName = array();


} // NewMapDataObject
