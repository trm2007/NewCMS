<?php

namespace NewCMS\Libs\Logger;

use TRMEngine\DiContainer\TRMStaticFactory;
use TRMEngine\Repository\TRMDataObjectsContainerRepository;
use TRMEngine\Repository\TRMRepositoryManager;

/**
 * с 2019.05.27 - класс хранилища для работы с комплексным объектом - посетитель сайта
 */
class NewComplexGuestRepository extends TRMDataObjectsContainerRepository
{

public function __construct(TRMRepositoryManager $RM, TRMStaticFactory $Factory)
{
    parent::__construct(NewComplexGuest::class, $RM, $Factory);
}


} // NewComplexGuestRepository