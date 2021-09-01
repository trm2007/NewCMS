<?php

namespace NewCMS\Controllers\AJAX;

use NewCMS\Domain\Exceptions\NewArticlesExceptions;
use NewCMS\Domain\NewArticle;
use NewCMS\Domain\NewArticlesType;
use NewCMS\Domain\NewComplexArticle;

/**
 * обработка AJAX-запросов для статей
 */
class NewAjaxArticleController extends NewAjaxCommonController
{

/**
 * возвращает объект NewComplexArticle в виде JSON
 */
public function actionGetComplexArticle()
{
    $ArticleId = file_get_contents('php://input');
    
    if( !$ArticleId ) { $ArticleId = 1; }
    
    $ArticleRep = $this->_RM->getRepository(NewComplexArticle::class); // new NewComplexArticleRepository();
    
    $ComplexArticle = $ArticleRep->getById($ArticleId);
    
    echo json_encode($ComplexArticle);
}

/**
 * рендерит клиенту JSON объект NewComplexArticle,
 * 
 */
public function actionGetEmptyComplexArticle()
{
    echo json_encode( $this->_RM->getRepository(NewComplexArticle::class)->getNewObject());
}

/**
 * сохраняет NewComplexArticle в БД
 */
public function actionUpdateComplexArticle()
{
    $json = file_get_contents('php://input');

    $ComplexArticle = new NewComplexArticle();

    // инициализируем объект из массива, полученного из JSON
    $ComplexArticle->initializeFromArray( json_decode($json, true) );

    $rep = $this->_RM->getRepositoryFor($ComplexArticle); // new NewComplexArticleRepository();

    $rep->update($ComplexArticle);
    $rep->doUpdate();

    echo json_encode($ComplexArticle);
}

/**
 * JSON-массив со списком всех возможных типов документов
 */
public function actionGetArticlesTypesList()
{
    $Rep = $this->_RM->getRepository(NewArticlesType::class); // new NewArticlesTypeRepository();

    echo json_encode($Rep->getAll());
}

/**
 * отправляет клиенту JSON-список документов с полученным идентификатором типа документов
 */
public function actionGetArticlesList()
{
    $ArticleTypeId = file_get_contents('php://input');
    
    if( $ArticleTypeId === null ) { $ArticleTypeId = 1; }
    $ArticleRep = $this->_RM->getRepository(NewArticle::class); // new NewArticleRepository();
    
    $ArticlesList = $ArticleRep->getBy( "articles", "Reserv", $ArticleTypeId );
    
    echo json_encode($ArticlesList);
}

/**
 * добавляет или обновляет в БД переданный объект NewArticlesType 
 * в зависимости от наличия ID у объекта
 */
public function actionSaveArticlesType()
{
    $json = file_get_contents('php://input');
    
    $ArticlesType = new NewArticlesType();
    $ArticlesType->initializeFromArray(json_decode($json, true));

    $Rep = $this->_RM->getRepository(NewArticlesType::class); // new NewArticlesTypeRepository();
    if( $ArticlesType->getId() === null )
    {
        $Rep->insert($ArticlesType);
    }
    else
    {
        $Rep->update($ArticlesType);
    }
    $Rep->doAll();
    
    echo json_encode($ArticlesType);
}

/**
 * удаляет из БД объект NewArticlesType 
 */
public function actionDeleteArticlesType()
{
    $json = file_get_contents('php://input');

    $ArticlesType = new NewArticlesType();
    $ArticlesType->initializeFromArray(json_decode($json, true));
    $Rep = $this->_RM->getRepository(NewArticlesType::class); // new NewArticlesTypeRepository();

    if( $ArticlesType->getId() === null )
    {
        throw new NewArticlesExceptions("Объект не зарегистрирован в системе!");
    }
    $Rep->delete($ArticlesType);

    $Rep->doDelete();
}


} // NewAjaxArticleController
