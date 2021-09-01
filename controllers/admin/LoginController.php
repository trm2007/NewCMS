<?php

namespace NewCMS\Controllers;

use NewCMS\Views\CMSBaseView;
use Symfony\Component\HttpFoundation\Request;
use TRMEngine\Controller\TRMLoginController;
use TRMEngine\Cookies\Exceptions\TRMAuthCookieException;
use TRMEngine\DiContainer\TRMDIContainer;
use TRMEngine\TRMDBObject;

class LoginController extends TRMLoginController
{
/**
 * @var string - имя GET-параметра,
 *  в котором сохранется исходный URI с которого перенаправили пользователя на страницу авторизации
 */
protected static $OriginatingUriArgName = "originating_uri";
/**
 * @var TRMDIContainer 
 */
protected $DIC;

public function __construct(Request $Request, TRMDIContainer $DIC)
{
    parent::__construct(
        $Request,
        \GlobalConfig::$ConfigArray["AuthCookieName"], // имя Cookie-фйла для авторизации
        "/login", // адрес , по которому перенаправляется не авторизованный пользоватьель
        self::$OriginatingUriArgName, // имя аргумента GET-запроса, в котором сохраняется исходный URI
        \GlobalConfig::$ConfigArray["AdminFolder"]  // адрес, на который произойдет переадресация авторизованного пользователя,
    );                                             // если не задан self::$OriginatingUriArgName
    $this->DIC = $DIC;
}

/**
 * пользовательская реализация функции,
 * должна проверять соответствие пароля и пользователя
 * 
 * @param string $name
 * @param string $password
 * @return boolean
 */
protected function checkPassword($name, $password)
{
    if( empty($name) )
    {
        $this->addStateString("Введите имя пользователя");
        return false;
    }
    //выбираем из БД пользователя с именем $name регистронезависимо !!!
    $row = $this->getUserPassword($name);

    if( empty($row) )
    {
        $this->addStateString("Пользователь не найден");
        return false;
    }

    // просто сравниваем пароли!
    // если нет отличие возвращаем true
    if( !strcmp($password, $row["AdminPass"]) )
    {
        return true;
    }

    $this->addStateString("Пароль не верный");
    return false;
}

/**
 * Проверяет наличие пользователя с указанным именем в системе
 * 
 * @param string $name
 * @return boolean
 */
protected function checkUser($name)
{
    if( empty($name) )
    {
        $this->addStateString("Введите имя пользователя");
        return false;
    }
    //выбираем из БД пользователя с именем $name регистронезависимо !!!
    $row = $this->getUserPassword($name);

    if( empty($row) )
    {
        $this->addStateString("Пользователь не найден");
        return false;
    }
    return true;
}

/**
 * получает пароль пользователя из БД,
 * если такого пользовтеля нет, то возвращает пустой массив
 * 
 * @param string $name
 * @return array
 */

protected function getUserPassword($name)
{
    //выбираем из БД пользователя с именем $name регистронезависимо !!!
    // потом надо сделать добавление пользователя проверяя наличие такого имени во всех регистрах !!!
    $query = "SELECT * FROM `admins` WHERE LOWER(`AdminName`) LIKE LOWER('{$name}')";
    $result =$this->DIC->get(TRMDBObject::class)->query($query);

    if( !$result || $result->num_rows <= 0 )
    {
        return array();
    }

    $row = $result->fetch_array(MYSQLI_BOTH);

    return array("AdminName" => $row["AdminName"], "AdminPass" => $row["AdminPass"]);
}


/**
 * пользовательская реализация,
 * в ней должны предприниматься действия, когда пользователь не авторизован и,
 * например, должен отображаться вид с формой входа
 */
public function renderLoginView()
{
    $this->setHeaders();
    // при заполнении формы self::$OriginatingUriArgName в строке запроса
    $uri = $this->Request->query->get( $this->OriginatingUriArgumentName, $this->DefaultUri );
    $name = $this->Request->request->get("name");
    $x_id = $this->Request->request->get("x_id");
    
    if( PHP_SESSION_ACTIVE === session_status() )
//    if( !empty($name) || !empty($x_id) )
    {
        if( empty($x_id) )
        {
            $x_id = session_id();
        }
        else if( $x_id !== session_id() )
        {
            throw new TRMAuthCookieException("Ключ сессии не совпадает! {$x_id}", 503);
        }
    }
    else if( PHP_SESSION_NONE === session_status() )
    {
        session_start();
        $x_id = session_id();
    }
    else if( PHP_SESSION_DISABLED === session_status() )
    {
        throw new TRMAuthCookieException("Механизм сессий не поддерживается на сервере! Авторизация невозможна.", 503);
    }
    
    
//\TRMEngine\Helpers\TRMLib::ap($_SESSION);exit;
    
//    if( PHP_SESSION_ACTIVE === session_status() )
//    {
//        if( !array_key_exists("id", $_SESSION) )
//        {
//            $_SESSION["id"] = session_id();
//        }
//        else if( $_SESSION["id"] !== session_id() )
//        {
//            throw new TRMAuthCookieException("Ключ сессии не совпадает!", 503);
//        }
//    }
//    else if( PHP_SESSION_NONE && 
//            (!isset($_SESSION) || !array_key_exists("id", $_SESSION) || empty($_SESSION["id"]) )
//        )
//    {
//        session_start();
//        $_SESSION["id"] = session_id();
//    }

//header("x-id:" . $_SESSION["id"] );
    $this->view = new CMSBaseView(null, null); // new ArticlesBaseView($this);
    $this->view->setVarsArray(\GlobalConfig::$ConfigArray);
    $this->view->setVar( $this->OriginatingUriArgumentName, $uri );
    $this->view->setVar("UserName", $name);
//    $this->view->setVar("x_id", $_SESSION["id"]);
    $this->view->setVar("x_id", $x_id);
    $this->view->setVar("StateString", $this->getStateString());

    $this->view->render();
}


} // LoginController