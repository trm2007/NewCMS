<?php

namespace NewCMS\Controllers;

use NewCMS\Domain\NewNews;

class NewsController extends BaseController
{
/**
 * количество новостей получаемых за один раз
 */
const DEFAULT_NEWS_LIMIT = 50;


public function actionBase()
{
//    $NewsRep = new NewNewsRepository();
    $NewsRep = $this->_RM->getRepository(NewNews::class);

    $NewsRep->setLimit( self::DEFAULT_NEWS_LIMIT );
    $NewsList = $NewsRep->getAll();
   
    $Title = "Новости сайта Подвесной.РУ";
    $KeyWords = $Title;
    $Description = $Title." - анонсы, новые материалы, изменения цен, информирование о режиме работы...";
    
    $this->view->setTitle( $Title );
    $this->view->setMeta( "keywords", $KeyWords );
    $this->view->setMeta( "description", $Description );

    $this->view->setVar("NewsList", $NewsList);
    $this->view->setVar("PageTitle", $Title );

    return $this->view->render();
}

public function actionMoreNews()
{
    $Count = $this->Request->query->getInt("count");
    $From = $this->Request->query->getInt("from");
    
//    $NewsRep = new NewNewsRepository();
    $NewsRep = $this->_RM->getRepository(NewNews::class);

    $NewsRep->setLimit( $Count, $From );
    $NewsList = $NewsRep->getAll();
   
    header('Access-Control-Allow-Origin: *');
    header("Content-Type: application/json; charset=utf-8");
    
    echo json_encode($NewsList);
}


} // NewsController