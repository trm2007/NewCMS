<?php

namespace NewCMS\Controllers\AJAX;

use NewCMS\Controllers\NewController;
use NewCMS\Domain\Exceptions\NewArticlesExceptions;
use NewCMS\Domain\NewNews;
use NewCMS\Views\CMSBaseView;
use NewCMS\Widgets\NewArticlesTitlesList;
use NewCMS\Widgets\NewLastProducts;
use TRMEngine\Helpers\TRMLib;

class AjaxWidgetsController extends NewController // TRMController // AuthController
{

public function actionArticlesTitlesList()
{
    if( $this->Request->request->getInt( "numberart", -1 ) === -1 ||
        $this->Request->request->getInt( "ArticlesType", -1 ) === -1 )
    {
        $StatusText = "";
        if( defined("DEBUG") )
        {
            $StatusText .= __METHOD__ . ": " . PHP_EOL;
        }
        $StatusText .= "Неверные параметры для загрузки списка названия статей!";
        throw new NewArticlesExceptions( $StatusText );
    }

    NewArticlesTitlesList::printArticlesTitles(
            $this->_RM->getRepository(\NewCMS\Domain\NewArticle::class), 
            $this->Request->request->getInt( "ArticlesType" ), 
            $this->Request->request->getInt( "numberart" ) 
        );
}

public function actionGetLastProductsHTML()
{
    try
    {
        NewLastProducts::render( $this->DIC->get(NewLastProducts::class) );
    }
    catch(\Exception $e)
    {
       TRMLib::sp($e->getMessage());
    }
}

public function actionGetLastNewsHTML()
{
    try
    {
        $NewsRepository = $this->_RM->getRepository(NewNews::class); // new NewNewsRepository();

        $NewsRepository->getDataSource()->setLimit(3);
        $NewsRepository->getDataSource()->setOrderField( "pubDate", false );

        $NewsList = $NewsRepository->getAll();


        $NewsView = new CMSBaseView("onenew", null);
        $NewsView->setPathToViews( ROOT . TOPIC . "/views/main/inc" );
        //ob_start();
        foreach( $NewsList as $OneNew )
        {
            $NewsView->setVarsArray( $OneNew["news"] );
            $NewsView->render();
        }
    }
    catch(\Exception $e)
    {
       TRMLib::sp($e->getMessage());
    }
}


}// class AjaxWidgetsController