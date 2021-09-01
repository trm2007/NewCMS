<?php

namespace NewCMS\Domain;

use TRMEngine\DataObject\TRMIdDataObject;

/**
 * класс производителя товаров
 */
class NewMetaTag extends TRMIdDataObject
{
/**
 * @var array - имя свойства для идентификатора объекта, обычно совпадает с именем ID-поля из БД
 */
static protected $IdFieldName = array( "new_metatags", "ID_MetaTag" );


} // NewMetaTag
