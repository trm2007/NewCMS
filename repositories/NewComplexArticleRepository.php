<?php

namespace NewCMS\Repositories;

use NewCMS\Domain\NewComplexArticle;
use NewCMS\Repositories\NewComplexCommonRepository;
use TRMEngine\DiContainer\TRMStaticFactory;
use TRMEngine\Repository\TRMRepositoryManager;

/**
 * с 2019.03.25 - класс хранилища для работы с комплексным объектом - статья и группы связанные со статьёй
 */
class NewComplexArticleRepository extends NewComplexCommonRepository
{

public function __construct(TRMRepositoryManager $RM, TRMStaticFactory $Factory)
{
    parent::__construct(NewComplexArticle::class, $RM, $Factory);
}


} // NewComplexArticleRepository