<?php

namespace NewCMS\Controllers\AJAX;

use NewCMS\Controllers\NewController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use TRMEngine\DiContainer\TRMDIContainer;

class NewAjaxCommonController extends NewController
{

/**
 * @param Request $Request
 * @param TRMDIContainer $DIC
 */
public function __construct(Request $Request, TRMDIContainer $DIC)
{
    if( defined("DEBUG") )
    {
        header('Access-Control-Allow-Origin: *');
    }
    header("Content-Type: application/json; charset=utf-8");

    parent::__construct($Request, $DIC);
}

/**
 * @param string $Message - сообщение, которое будет отправлено в виде JSON - объекта
 * @param int $Code - код, с которым заверщится выполнение скрипта, по умолчанию 503
 * 
 * @return Response
 */
public function exitOnError($Message, $Code = 503)
{
    return new Response( $Message, $Code );
}


} // NewAjaxCommonController