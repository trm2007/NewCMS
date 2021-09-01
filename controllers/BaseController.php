<?php

namespace NewCMS\Controllers;

use NewCMS\Views\ArticlesBaseView;
use Symfony\Component\HttpFoundation\Request;
use TRMEngine\DiContainer\TRMDIContainer;
use TRMEngine\PathFinder\Exceptions\TRMControllerNotFoundedException;

/**
 * базовый контроллер с общим конструктором для большинства создаваемых контроллеров в приложении
 */
abstract class BaseController extends NewController
{

function __construct(Request $Request, TRMDIContainer $DIC)
{
    parent::__construct($Request, $DIC);

    $this->view = new ArticlesBaseView($this);
    
    if( !is_dir($this->view->getPathToViews()) )
    {
        throw new TRMControllerNotFoundedException( "Не найден вид [{$this->view->getPathToViews()}] !", 404 );
    }
//    $this->view->setVarsArray(\GlobalConfig::$ConfigArray);
    $this->view->setViewName(strtolower($this->CurrentActionName));
}


} // BaseController