<?php

namespace NewCMS\Repositories;

use NewCMS\Domain\NewComplexProduct;
use NewCMS\Repositories\NewComplexCommonRepository;
use TRMEngine\DiContainer\TRMStaticFactory;
use TRMEngine\Repository\TRMRepositoryManager;

/**
 * с 2018.07.08 - основной класс хранилища для работы с продуктом вмсесте со вспомогательными объектами
 *
 * @author TRM
 */
class NewComplexProductRepository extends NewComplexCommonRepository
{


public function __construct(TRMRepositoryManager $RM, TRMStaticFactory $Factory)
{
    parent::__construct(NewComplexProduct::class, $RM, $Factory);
}


} // NewComplexProductRepository