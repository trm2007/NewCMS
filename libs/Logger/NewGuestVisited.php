<?php

namespace NewCMS\Libs\Logger;

use TRMEngine\DataObject\TRMIdDataObject;

/**
 * информация о посещенных гостем страницах, 
 * для сбора посещенных страниц на сайте
 */
class NewGuestVisited extends TRMIdDataObject
{
/**
 * @var array - имя свойства для идентификатора объекта, обычно совпадает с именем ID-поля из БД
 */
static protected $IdFieldName = array( "new_guest_visited", "session_id" );


public function __construct()
{
    $this->setURL( filter_input(INPUT_SERVER, "REQUEST_URI") );
    $this->setSessionId( session_id() );
    $this->setTimeStart( date("Y-m-d H:i:s") );
    $this->setTimeStop( null );
}

public function setSessionId( $SessionId )
{
    $this->setData("new_guest_visited", "session_id", $SessionId);
}
public function setURL( $URL )
{
    $this->setData("new_guest_visited", "url", $URL);
}
public function setTimeStart( $TimeStart )
{
    $this->setData("new_guest_visited", "time_start", $TimeStart);
}
public function setTimeStop( $TimeStop )
{
    $this->setData("new_guest_visited", "time_stop", $TimeStop);
}


} // NewGuestVisited
