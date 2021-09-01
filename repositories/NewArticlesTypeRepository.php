<?php

namespace NewCMS\Repositories;

use NewCMS\Domain\NewArticlesType;
use TRMEngine\DataMapper\TRMDataMapper;
use TRMEngine\DataObject\Interfaces\TRMDataObjectsCollectionInterface;
use TRMEngine\DataObject\TRMDataObjectsCollection;
use TRMEngine\DataSource\Interfaces\TRMDataSourceInterface;

/**
 * класс репозитория для объекта "тип документов"
 */
class NewArticlesTypeRepository extends NewIdTranslitRepository
{
static protected $DataObjectMap = array(
    "articlestype" => array(
        TRMDataMapper::STATE_INDEX => TRMDataMapper::FULL_ACCESS_FIELD,
        TRMDataMapper::FIELDS_INDEX => array(
            "ID_articlestype" => array(
                TRMDataMapper::KEY_INDEX => "PRI",
            ),
        ),
    ),
);

/**
 * @var bool - если установлен, 
 * то из БД будут выбираться только типы документов со значение поля Present != 0,
 * по умолчанию выбираются все типы документов
 */
protected $PresentFlag = false;


public function __construct(TRMDataSourceInterface $DataSource)
{
    parent::__construct(NewArticlesType::class, $DataSource);
}


public function getPresentFlag()
{
    return $this->PresentFlag;
}
/**
 * @param bool $PresentFlag
 */
public function setPresentFlag($PresentFlag = true)
{
    $this->PresentFlag = $PresentFlag;
}

/**
 * возвращает коллекцию с типами документов,
 * использую при выборке PresentFlag
 * 
 * @param TRMDataObjectsCollectionInterface $Collection - если задан, 
 * то коллекция сохранится в этом объекте
 * 
 * @return TRMDataObjectsCollection - коллекция с типами документов
 */
public function getAll(TRMDataObjectsCollectionInterface $Collection = null)
{
    if( $this->PresentFlag )
    {
        $this->addCondition("articlestype", "Present", 1);
    }

    return parent::getAll($Collection);
}


} // NewArticlesTypeRepository