<?php

namespace NewCMS\MapData;

use TRMEngine\DataMapper\TRMDataMapper;
use TRMEngine\DataMapper\TRMSafetyFields;
use TRMEngine\DataSource\Interfaces\TRMDataSourceInterface;
use TRMEngine\Repository\TRMIdDataObjectRepository;

/**
 * с 2019.11.21 - выбирает из БД только те данные, которые указаны в $DataObjectMap,
 * работает с объектами типа NewMapDataObject
 *
 * @author TRM
 */
class NewMapDataObjectRepository extends TRMIdDataObjectRepository
{


public function __construct(TRMDataSourceInterface $DataSource)
{
    parent::__construct(NewMapDataObject::class, $DataSource);

    $this->DataMapper = new TRMSafetyFields($DataSource->getDBObject());
}

/**
 * Устанавливает DataMapper на основе данных из массива $DataObjectMap,
 * который должен иметь вид array( ObjectName1 => array( FieldName1 => array(key => ..., State => ...) ... ) ... )
 * 
 * @param array $DataObjectMap
 * @param int $DefaultState
 */
public function setDataMapperArray(array &$DataObjectMap, $DefaultState = TRMDataMapper::READ_ONLY_FIELD)
{
    $this->DataMapper->setFieldsArray($DataObjectMap, $DefaultState);
    $this->DataMapper->completeOnlyExistsFieldsFromDB();
    $IDArr = $this->DataMapper->getIdFieldName();
    NewMapDataObject::setIdFieldName( $IDArr );
}


} // NewMapDataRepository