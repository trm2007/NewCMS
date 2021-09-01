<?php

namespace NewCMS\Repositories;

use NewCMS\Domain\NewComplexGroup;
use NewCMS\Repositories\NewComplexCommonRepository;
use TRMEngine\DiContainer\TRMStaticFactory;
use TRMEngine\Repository\TRMRepositoryManager;

/**
 * с 2018.07.08 - основной класс хранилища для работы с продуктом вмсесте со вспомогательными объектами
 *
 * @author TRM
 */
class NewComplexGroupRepository extends NewComplexCommonRepository
{


public function __construct(TRMRepositoryManager $RM, TRMStaticFactory $Factory)
{
    parent::__construct(NewComplexGroup::class, $RM, $Factory);
}

/**
 * @return boolean - флаг, указывающий на необходимость собирать всю информацию из родительских групп
 */
public function getFullParentInfoFlag()
{
    return $this->MainDataObjectRepository->getFullParentInfoFlag();
}
/**
 * @param boolean $FullParentInfoFlag - флаг, 
 * указывающий на необходимость собирать всю информацию из родительских групп
 */
public function setFullParentInfoFlag($FullParentInfoFlag=true)
{
    $this->MainDataObjectRepository->setFullParentInfoFlag($FullParentInfoFlag);
}


} // NewComplexGroupRepository