<?php
use TRMEngine\TRMController;
use Symfony\Component\HttpFoundation\Request;

abstract class AuthController extends TRMController
{

/**
 * в этом конструкторе создается класс куки с авторизацией 
 * и если валидация пройдена, тогда продолжаем работать, иначе выход!
 */
function __construct(Request $Request)
{
    try
    {
        $cookie = new AuthCookie();
        if($cookie)
        {
            $cookie->validate();
            parent::__construct($Request);
        }
    }
    catch (\TRMEngine\Exceptions\AuthException $e)
    {
        echo "Не авторизован: ". $e->getMessage();
        exit;
    }
}


} // AuthController