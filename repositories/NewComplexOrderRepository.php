<?php

namespace NewCMS\Repositories;

use NewCMS\Domain\NewComplexOrder;
use NewCMS\Repositories\NewComplexCommonRepository;
use TRMEngine\DiContainer\TRMStaticFactory;
use TRMEngine\Repository\TRMRepositoryManager;

/**
 * с 2019.05.02 - класс хранилища для работы с комплексным объектом заказа
 */
class NewComplexOrderRepository extends NewComplexCommonRepository
{

public function __construct(TRMRepositoryManager $RM, TRMStaticFactory $Factory)
{
    parent::__construct(NewComplexOrder::class, $RM, $Factory);
}


} // NewComplexOrderRepository