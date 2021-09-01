<?php

namespace NewCMS\Controllers\AJAX;

use NewCMS\Controllers\AJAX\NewAjaxCommonController;
use NewCMS\Domain\NewVendor;
use NewCMS\Repositories\NewVendorRepository;
use Symfony\Component\HttpFoundation\Request;
use TRMEngine\DiContainer\TRMDIContainer;

/**
 * обработка AJAX-запросов для производителей
 */
class NewAjaxVendorController extends NewAjaxCommonController
{
/**
 * @var NewVendorRepository
 */
protected $Rep;

public function __construct(Request $Request, TRMDIContainer $DIC)
{
    parent::__construct($Request, $DIC);
    $this->Rep = $this->_RM->getRepository(NewVendor::class);
}

/**
 * возвращает объект NewVendor в виде JSON
 */
public function actionGetVendor()
{
    $VendorId = file_get_contents('php://input');
    
    if( !$VendorId ) { $VendorId = 3; }
    
    $this->Rep->setFullParentInfoFlag();
    
    $Vendor = $this->Rep->getById($VendorId);
    
    echo json_encode($Vendor);
}

/**
 * рендерит клиенту JSON объект NewComplexArticle,
 * 
 */
public function actionGetEmptyVendor()
{
    echo json_encode( $this->Rep->getNewObject());
}

/**
 * сохраняет NewComplexArticle в БД
 */
public function actionUpdateVendor()
{
    $json = file_get_contents('php://input');

    $Vendor = new NewVendor();

    // инициализируем объект из массива, полученного из JSON
    $Vendor->initializeFromArray( json_decode($json, true) );

    $this->Rep->update($Vendor);
    $this->Rep->doUpdate();

    echo json_encode($Vendor);
}

public function actionGetVendorsList()
{
    echo json_encode( $this->Rep->getAll() );
}


} // NewAjaxVendorController
