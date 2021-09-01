<?php

namespace NewCMS\Libs\Logger;

use NewCMS\Libs\Logger\Exceptions\NewLoggerIpException;
use NewCMS\Libs\Logger\Exceptions\NewLoggerSessionException;
use TRMEngine\DataObject\TRMIdDataObject;

/**
 * информация о посетителях сайта
 *
 * @date 2019-05-17
 */
class NewLogger extends TRMIdDataObject
{
/**
 * @var array - имя свойства для идентификатора объекта, обычно совпадает с именем ID-поля из БД
 */
static protected $IdFieldName = array( "new_guest_info", "id" );


public function __construct()
{
    $SessionState = session_status();
    if( $SessionState === PHP_SESSION_DISABLED )
    {
        throw new NewLoggerSessionException("Механизм сессий не поддерживается!");
    }
    if( $SessionState === PHP_SESSION_NONE && !session_start() )
    {
        throw new NewLoggerSessionException();
    }
    if( !isset( $_SESSION["SessionStartTime"] ) )
    {
        // экономим на вызове time()
        $Time = time();
        // старт с текущего времени
        $this->setTime( $Time );
        $_SESSION["SessionStartTime"] = $Time;
    }
    else
    {
        // восстанваливает из сессии
        $this->setTime( filter_var($_SESSION["SessionStartTime"], FILTER_SANITIZE_NUMBER_INT) );
    }

    $IP = $this->getRealIpAddr();
    if( !$IP )
    {
        throw new NewLoggerIpException();
    }
    $this->setIP($IP);
    // в info помещаем информацию о клиенте
    $this->setInfo( $this->getUserAgent() );
    $this->setId(session_id());
}


/**
 * @return string - IP текущей сессии
 */
public function getIP()
{
    return $this->getData( "new_guest_info", "ip" );
}
/**
 * @param string $IP - устанавливает IP текущей сессии
 */
public function setIP($IP)
{
    $this->setData( "new_guest_info", "ip", $IP );
}
/**
 * @return string - возвращает время (как правило старта) сессии
 */
public function getTime()
{
    return $this->setData( "new_guest_info", "time" );
}
/**
 * @param string $Time - устанавливает время (как правило старта) сессии
 */
public function setTime($Time)
{
    $this->setData( "new_guest_info", "time", $Time );
}
/**
 * @return string  - информация о пользователе
 */
public function getInfo()
{
    return $this->getData( "new_guest_info", "info" );
}
/**
 * @param string $Info - устанавливает информацию о пользователе
 */
public function setInfo($Info)
{
    $this->setData( "new_guest_info", "info", $Info );
}

/**
 * @return string - определяет и возвращает в виде строки IP-клиента
 */
protected function getRealIpAddr()
{
    if( !empty($_SERVER['HTTP_CLIENT_IP'] ) )
    {
        return filter_input( INPUT_SERVER , "HTTP_CLIENT_IP"); // $_SERVER['HTTP_CLIENT_IP'];
    }
    else if( !empty($_SERVER['HTTP_X_FORWARDED_FOR']) ) 
    {
        return filter_input( INPUT_SERVER , "HTTP_X_FORWARDED_FOR"); // $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    else if( !empty($_SERVER['REMOTE_ADDR']) ) 
    {
        return filter_input( INPUT_SERVER , "REMOTE_ADDR"); // $_SERVER['REMOTE_ADDR'];
    }
    return null;
}
/**
 * @return string - возвращает USER_AGENT из $_SERVER
 */
protected function getUserAgent()
{
    return filter_input( INPUT_SERVER , "HTTP_USER_AGENT"); // $_SERVER['HTTP_USER_AGENT'];
}


/**
 * @return string - генерация простого session ID через вызов sha1 для времени
 */
protected function generateSessionId()
{
    if( $this->getData("new_guest_info", "id") !== null )
    {
        return;
    }
    return sha1(time());
}


} // NewLogger
