<?php

namespace NewCMS\Controllers;

use NewCMS\Domain\NewSearchQuery;
use NewCMS\Libs\NewSearchObject;
use NewCMS\Repositories\NewSearchQueryRepository;
use TRMEngine\Exceptions\TRMObjectCreateException;

/**
 *  контроллер для поиска товаров
 */
class SearchController extends BaseController
{

/**
 * поиск товаров в каталоге
 * 
 * @return string - содержимое страницы
 */
public function actionIndex()
{
    $this->view->addCss( TOPIC . "/css/search.css" , true);

    $Quest = $this->Request->query->get("quest", "");
    if( !empty($Quest) )
    {
        $SearchQuery = new NewSearchQuery();
        $SearchQuery->setQueryText($Quest);
        
        $Rep = new NewSearchQueryRepository();
        $Rep->insert($SearchQuery);
        $Rep->doInsert();
    }
    
    $this->view->setVar( "quest", $Quest );
    $this->view->setVar( "andor", $this->Request->query->get("andor", "") );
    $this->view->setVar( "translit", $this->Request->query->get("translit", 0) );

    try
    {
        $SearchObject = new NewSearchObject(
                $this->Request->query->get("quest"), 
                $this->Request->query->get("andor"), 
                $this->Request->query->get("translit", 0)
            );
        $SearchObject->getResult($this->getDBObject());
        $this->view->setVar("SearchObject", $SearchObject );
    }
    catch (TRMObjectCreateException $e)
    {
        $this->view->setVar("SearchResultText", $e->getMessage() );
    }

    $this->view->setTitle( \GlobalConfig::$ConfigArray["SearchTitle"]);
    $this->view->setMeta("description", \GlobalConfig::$ConfigArray["SearchTitle"]);
    $this->view->setMeta("keywords", \GlobalConfig::$ConfigArray["CommonKeyWords"] . ", поиск");
    $this->view->setVar("PageTitle", \GlobalConfig::$ConfigArray["SearchTitle"]);

    return $this->view->render();
}


/**
 * поиск товаров в каталоге
 * 
 * @return string - содержимое страницы
 */
public function actionYandex()
{
    $this->view->addCss( TOPIC . "/css/search.css" , true);

    $this->view->setTitle( \GlobalConfig::$ConfigArray["SearchTitle"]);
    $this->view->setMeta("description", \GlobalConfig::$ConfigArray["SearchTitle"]);
    $this->view->setMeta("keywords", \GlobalConfig::$ConfigArray["CommonKeyWords"] . ", поиск");
    $this->view->setVar("PageTitle", \GlobalConfig::$ConfigArray["SearchTitle"]);

    return $this->view->render();
}


} // SearchController