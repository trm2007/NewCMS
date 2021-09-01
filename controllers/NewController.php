<?php

namespace NewCMS\Controllers;

use Symfony\Component\HttpFoundation\Request;
use TRMEngine\Cache\TRMCache;
use TRMEngine\Controller\TRMController;
use TRMEngine\DiContainer\TRMDIContainer;
use TRMEngine\Repository\TRMRepositoryManager;
use TRMEngine\TRMDBObject;

/**
 * базовый контроллер с общим конструктором для большинства создаваемых контроллеров в приложении
 */
abstract class NewController extends TRMController
{
/**
 * @var TRMRepositoryManager - менеджер репозиториев
 */
protected $_RM;
/**
 * @var TRMDIContainer 
 */
protected $DIC;

/**
 * @param Request $Request
 * @param TRMDIContainer $DIC
 */
public function __construct( Request $Request, TRMDIContainer $DIC )
{
    parent::__construct($Request);
    
    $this->DIC = $DIC;
    $this->_RM = $DIC->get(TRMRepositoryManager::class);
}

/**
 * @return TRMDIContainer - объект кэша
 */
public function getDIContainer()
{
    return $this->DIC;
}

/**
 * @return TRMRepositoryManager
 */
public function getRepositoryManager()
{
    return $this->_RM;
}


/**
 * @return TRMCache - объект кэша
 */
public function getCache()
{
    return $this->DIC->get(TRMCache::class);
}

/**
 * @return TRMDBObject - объект кэша
 */
public function getDBObject()
{
    return $this->DIC->get(TRMDBObject::class);
}


} // NewController