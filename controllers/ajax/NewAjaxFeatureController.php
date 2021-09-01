<?php

namespace NewCMS\Controllers\AJAX;

use NewCMS\Domain\NewFeature;
use NewCMS\Repositories\NewFeatureRepository;
use Symfony\Component\HttpFoundation\Request;
use TRMEngine\DiContainer\TRMDIContainer;

/**
 * обработка AJAX-запросов для новостей
 */
class NewAjaxFeatureController extends NewAjaxCommonController
{
/**
 * @var NewFeatureRepository
 */
protected $Rep;

public function __construct(Request $Request, TRMDIContainer $DIC)
{
    parent::__construct($Request, $DIC);
    $this->Rep = $this->_RM->getRepository(NewFeature::class);
}


/**
 * возвращает объект NewVendor в виде JSON
 */
public function actionGetFeature()
{
    $FeatureId = file_get_contents('php://input');
    
    echo json_encode( $this->Rep->getById($FeatureId) );
}

/**
 * рендерит клиенту JSON объект NewComplexArticle,
 * 
 */
public function actionGetEmptyFeature()
{
    echo json_encode( $this->Rep->getNewObject() );
}

/**
 * сохраняет NewComplexArticle в БД
 */
public function actionUpdateFeature()
{
    $json = file_get_contents('php://input');

    $Feature = new NewFeature();

    // инициализируем объект из массива, полученного из JSON
    $Feature->initializeFromArray( json_decode($json, true) );

    $this->Rep->update($Feature);
    $this->Rep->doUpdate();

    echo json_encode($Feature);
}

/**
 * возвращает полный список характеристик
 */
public function actionGetFeaturesList()
{
    echo json_encode( $this->Rep->getAll() );
}


} // NewAjaxFeatureController
