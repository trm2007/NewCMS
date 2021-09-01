<?php

namespace NewCMS\Repositories;

use TRMEngine\DataMapper\TRMSafetyFields;
use TRMEngine\DataSource\Interfaces\TRMDataSourceInterface;
use TRMEngine\Repository\TRMIdDataObjectRepository;

/**
 * с 2018.07.28 - основной класс хранилища для работы с продуктом из таблицы table1 без вспомогательных объектов,
 * но с описанием из отдельной таблицы - goodsdescription и единицами измерения - unit
 *
 * @author TRM
 */
abstract class NewRepository extends TRMIdDataObjectRepository
{

/**
 * @param string $objectclassname - имя класса для объектов, за которые отвечает этот Repository
 */
public function __construct($objectclassname, TRMDataSourceInterface $DataSource)
{
    parent::__construct($objectclassname, $DataSource);

    $this->DataMapper = new TRMSafetyFields($DataSource->getDBObject());
    $this->DataMapper->setFieldsArray(static::$DataObjectMap);
    $this->DataMapper->completeSafetyFieldsFromDB();
    $this->DataMapper->getIdFieldName();
}


} // NewLiteProductRepository