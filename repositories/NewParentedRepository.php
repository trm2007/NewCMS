<?php

namespace NewCMS\Repositories;

use TRMEngine\DataMapper\TRMSafetyFields;
use TRMEngine\DataObject\Interfaces\TRMDataObjectInterface;
use TRMEngine\DataObject\Interfaces\TRMDataObjectsCollectionInterface;
use TRMEngine\DataSource\Interfaces\TRMDataSourceInterface;
use TRMEngine\Exceptions\TRMSqlQueryException;
use TRMEngine\Repository\TRMParentedDataObjectRepository;

/**
 * Общий контроллер для коллекций, соединенных таблицей МНОГОЕ-КО-МНОГОМУ,
 * изменены методы Update, перед обновлением 
 * данные из таблицы МНОГОЕ-КО-МНОГОМУ должны сначала удаляться,
 * а потом нужно записывать новые (обновленные) данные
 *
 * @author TRM 2019-05-07
 */
abstract class NewParentedRepository extends TRMParentedDataObjectRepository
{
/**
 * @var bool - если этот флаг установлен в true, то перед добавлением данной 
 * дочерней коллекции из связывающей таблицы будут удалены все старые связи 
 * по ID-родителя
 */
protected $ClearAllRelationBeforeUpdateFlag = true;


public function __construct($objectclassname, TRMDataSourceInterface $DataSource) 
{
    parent::__construct($objectclassname, $DataSource);

    $SafetyFields = new TRMSafetyFields($DataSource->getDBObject());
    $SafetyFields->setFieldsArray(static::$DataObjectMap);
    $SafetyFields->completeSafetyFieldsFromDB();
    $SafetyFields->sortObjectsForRelationOrder(true);

    $this->setDataMapper($SafetyFields);
}

/**
 * для соединяющей таблицы МНОГОЕ-КО-МНОГОМУ данные не обновляются,
 * старые должны полностью удаляться из таблицы для всех ID-родителя,
 * а обновляемые объекты добавляются в коллекцию вставляемых CollectionToInsert
 * 
 * @param TRMDataObjectInterface $DataObject
 */
public function update(TRMDataObjectInterface $DataObject)
{
    parent::insert($DataObject);
}
/**
 * для соединяющей таблицы МНОГОЕ-КО-МНОГОМУ данные не обновляются,
 * старые должны полностью удаляться из таблицы для всех ID-родителя,
 * а обновляемые объекты добавляются в коллекцию вставляемых CollectionToInsert
 * 
 * @param TRMDataObjectsCollectionInterface $Collection
 */
public function updateCollection(TRMDataObjectsCollectionInterface $Collection)
{
    parent::insertCollection($Collection);
}
/**
 * для соединяющей таблицы МНОГОЕ-КО-МНОГОМУ данные не обновляются,
 * старые должны полностью удаляться из таблицы для всех ID-родителя,
 * а обновляемые объекты добавляются в коллекцию вставляемых CollectionToInsert
 * 
 * @param boolean $ClearCollectionFlag
 */
public function doUpdate($ClearCollectionFlag = true)
{
//    if( $this->ClearAllRelationBeforeUpdateFlag )
//    {
//        try
//        {
//            $this->deleteAllRelationsForParents();
//        }
//        catch(TRMSqlQueryException $e)
//        {}
//    }
    parent::doInsert($ClearCollectionFlag);
}

/**
 * для комплексного объекта перед обновлением 
 * удаляются все данные дочерних коллекций из БД.
 * 
 * @throws TRMSqlQueryException
 */
//private function deleteAllRelationsForParents()
//{
//    $DeleteQuery = "";
//    foreach( $this->CollectionToInsert as $DataObject )
//    {
//        $ParentIdFieldName = $DataObject::getParentIdFieldName();
//        $TableName = $ParentIdFieldName[0];
//        $IdFieldName = $ParentIdFieldName[1];
//        $ParentId = $DataObject->getParentDataObject()->getId();
//        $DeleteQuery .= "DELETE FROM `{$TableName}` WHERE `{$IdFieldName}`={$ParentId};";
//    }
//    if( !empty($DeleteQuery) )
//    {
//        $this->DataSource->completeMultiQuery($DeleteQuery);
//    }
//}


} // NewParentedRepository