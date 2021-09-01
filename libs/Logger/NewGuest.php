<?php

namespace NewCMS\Libs\Logger;

use TRMEngine\DataObject\TRMIdDataObject;

/**
 * информацмя о госте (посетители) сайта
 *
 * @date 2019-05-02
 */
class NewGuest extends TRMIdDataObject
{
/**
 * @var array - имя свойства для идентификатора объекта, обычно совпадает с именем ID-поля из БД
 */
static protected $IdFieldName = array( "new_guest_info", "id" );


} // NewGuest
