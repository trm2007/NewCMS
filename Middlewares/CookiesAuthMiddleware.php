<?php

namespace NewCMS\Middlewares;

use TRMEngine\Middlewares\TRMCookiesAuthMiddleware;
use TRMEngine\TRMDBObject;

/**
 *
 * @author TRM
 * @version 2019-03-21
 */
class CookiesAuthMiddleware extends TRMCookiesAuthMiddleware
{
/**
 * @var string - Путь, по которому будет перенаправлен не авторизованный пользователь
 */
protected $UnAuthURL = "/login";
/**
 * @var TRMDBObject 
 */
protected $DBO;

public function __construct(TRMDBObject $DBO)
{
    parent::__construct( 
            \GlobalConfig::$ConfigArray["AuthCookieName"], // имя Cookie-фйла для авторизации
            "/login", // адрес , по которому перенаправляется не авторизованный пользоватьель
            "originating_uri"); // имя аргумента GET-запроса, в котором сохраняется исходный URI
    $this->DBO = $DBO;
}

/**
 * проверяет наличие пользователя в БД
 * 
 * @param string $username - имя проверяемого пользователя
 * 
 * @return boolean - если пользователь найден возвращает true
 */
protected function checkUser($username)
{
    //выбираем из БД пользователя с именем $username
    $query = "SELECT `AdminName` FROM `admins` WHERE `AdminName` LIKE '{$username}'";
    $result = $this->DBO->query($query);

    if( !$result || !$result->num_rows )
    {
        return false;
    }

    return true;
}

} // CookiesAuthMiddleware
