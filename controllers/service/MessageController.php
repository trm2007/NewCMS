<?php

namespace NewCMS\Controllers;

use Symfony\Component\HttpFoundation\Request;
use TRMEngine\DiContainer\TRMDIContainer;
use TRMEngine\EMail\Exceptions\TRMEMailExceptions;
use TRMEngine\EMail\TRMEMail;

/**
 * контроллер для работы с сообщениями, обратной связью
 */
class MessageController extends BaseController
{
/**
 * @var string - сообщение с результатом отправки сообщения или ошибка
 */
protected $StatusText;

    
public function __construct(Request $Request, TRMDIContainer $DIC)
{
    parent::__construct($Request, $DIC);
    $this->StatusText = '';
}

/**
 * вывод страницы с формой обратной связи
 * 
 * @return string
 */
public function actionForm()
{
    if( strlen($this->StatusText)>0 ) { $this->view->setVar("Text", $this->StatusText); }

    $this->view->setTitle("Обратная связь");
    $this->view->setVar("coding", \GlobalConfig::$ConfigArray["Charset"]);
    $this->view->setVarsArray(\GlobalConfig::$ConfigArray);

    if( count( $this->Request->request->all() ) )
    {
        $this->view->setVarsArray( $this->Request->request->all() );
    }
    return $this->view->render();
}

/**
 * отправка сообщения от пользователя через POST-запрос 
 * и вызов actionForm для вывода формы сообщения
 * 
 * @return string
 */
public function actionSend()
{
    $emailaddress = $this->Request->request->get("email");
    $fio = $this->Request->request->get("fio");
    $message = $this->Request->request->get("message");
    $phone = $this->Request->request->get("phone");

    if( empty($emailaddress) )
    {
        throw new TRMEMailExceptions("Передан пустой E-mail адрес!");
    }
    if( empty($fio) )
    {
        $fio = $emailaddress;
    }

    try
    {

        $email = new TRMEMail();
    
        $email->setCoding($this->Request->request->get("coding"));
        $email->setEmailFrom($emailaddress);
        $email->setNameFrom($fio);
        $email->setEmailTo(\GlobalConfig::$ConfigArray["email"]); // "info@superventilator.ru");

        $email->setConfig( CONFIG . "/emailconfig.php" );

        $email->setMessage( $message 
                            . "<br>--------------------------------"
                            . "<br>Tel: " . $phone
                            . "<br>E-mail: " . $emailaddress
                            . "<br>Name: " . $fio);

        $email->setSubject( \GlobalConfig::$ConfigArray["SiteName"] );
        $email->setReplyTo( $emailaddress, $fio );

        $email->sendEmail();
        $this->StatusText = "Сообщение отправлено!!!";
    }
    catch (TRMEMailExceptions $e)
    {
        $this->StatusText = "Ошибка при отправке сообщения!!!<br>";
        $this->StatusText .= "Возможно, адрес [" . $emailaddress . "] "
                . "указан не верно!";
        if( defined("DEBUG") && DEBUG )
        {
            $this->StatusText .= PHP_EOL . "<br>Исключение: " . $e->getMessage();
        }
    }

    echo $this->StatusText;
//    $this->view->setViewName("form");
//    return $this->actionForm();
}


} // MessageController