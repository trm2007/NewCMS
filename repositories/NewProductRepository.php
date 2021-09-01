<?php

namespace NewCMS\Repositories;

use NewCMS\Domain\NewProduct;
use TRMEngine\DiContainer\TRMStaticFactory;
use TRMEngine\Repository\TRMDataObjectsContainerRepository;
use TRMEngine\Repository\TRMRepositoryManager;

/**
 * с 2018.07.01 - основной класс хранилища для работы с продуктом из таблицы table1, group, vendors
 * но без вспомогательных объектов complect, features, images...
 *
 * @author TRM
 */
class NewProductRepository extends TRMDataObjectsContainerRepository
{


public function __construct(TRMRepositoryManager $RM, TRMStaticFactory $Factory)
{
    parent::__construct(NewProduct::class, $RM, $Factory);
}


} // NewProductRepository