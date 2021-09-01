<?php

namespace NewCMS\Controllers\AJAX;

use NewCMS\Controllers\AJAX\NewAjaxCommonController;
use NewCMS\MapData\NewMapDataObject;
use NewCMS\MapData\NewMapDataObjectRepository;
use TRMEngine\DataArray\TRMDataArray;
use TRMEngine\DataMapper\TRMDataMapper;
use TRMEngine\Exceptions\TRMException;
use TRMEngine\Helpers\TRMLib;

/**
 * обработка AJAX-запросов для производителей
 */
class NewAjaxQueryController extends NewAjaxCommonController
{
/**
 * 
 * @param array $IdsArr - массив параметров, имена объектов-таблиц 
 * и имена полей, которые нужно выбрать, возможно их значения для условий поиска
 * @param NewMapDataObjectRepository $Rep - репозиторий
 * @return TRMDataArray - массив коллекций с объектами, 
 * удовлетворяющими параметрам запроса
 */
protected function getQuery(array &$IdsArr, NewMapDataObjectRepository $Rep)
{
    $Result = new TRMDataArray();

    foreach( $IdsArr["query"] as $ObjectName => $Fields )
    {
        $FieldsType = array();
        $FieldsType[$ObjectName] = array(
            TRMDataMapper::FIELDS_INDEX => array(),
        );
        foreach( $Fields as $FieldName => $Value )
        {
            $FieldsType[$ObjectName][TRMDataMapper::FIELDS_INDEX][$FieldName] = array(
                TRMDataMapper::STATE_INDEX => TRMDataMapper::READ_ONLY_FIELD
            );
            if( $Value !== null ) // !empty($Value) )
            {
                $Rep->addCondition($ObjectName, $FieldName, $Value);
            }
        }
        $Rep->setDataMapperArray($FieldsType);
        $Result->push( $Rep->getAll() );
    }
    return $Result;
}

/**
 * 
 * @param array $IdsArr
 * @param NewMapDataObjectRepository $Rep
 * @throws TRMException
 */
protected function postMutations(array &$IdsArr, NewMapDataObjectRepository $Rep)
{
    $UpdateFieldsType = array();

/**
* НУЖНО ПРИДУМАТЬ СЕКЦИЮ WHERE 
* ДЛЯ ПЕРЕДАЧИ ПАРАМЕТРОВ ЗАПРОСА НА ОБНОВЛЕНИЕ
*/
//"mutations": {
//[
//    {
//        "table1": {
//            "id": "ID_price",
//            "data": { "ID_price": IdPresetObject.id, "present": IdPresetObject.value }
//        }
//    }
//]
    foreach( $IdsArr["mutations"] as $Objects )
    {
        foreach( $Objects as $ObjectName => $Data )
        {
            if(!key_exists("id", $Data))
            {
                throw new TRMException("Неверный формат данных для записи! Отсутсвует секция [id]");
            }
            if(!key_exists("data", $Data))
            {
                throw new TRMException("Неверный формат данных для записи! Отсутсвует секция [data]");
            }
            $UpdateFieldsType[$ObjectName] = array(
                TRMDataMapper::FIELDS_INDEX => array(),
            );
            $TmpObject = new NewMapDataObject();
            foreach( $Data["data"] as $FieldName => $Value )
            {
                $UpdateFieldsType[$ObjectName][TRMDataMapper::FIELDS_INDEX][$FieldName] = array(
                    TRMDataMapper::STATE_INDEX => TRMDataMapper::FULL_ACCESS_FIELD
                );
                if( $FieldName == $Data["id"])
                {
                    $UpdateFieldsType[$ObjectName][TRMDataMapper::FIELDS_INDEX][$FieldName][TRMDataMapper::KEY_INDEX] = "PRI";
                }
                $TmpObject->setData($ObjectName, $FieldName, $Value);
            }
            $Rep->setDataMapperArray($UpdateFieldsType);
            $Rep->update($TmpObject);
            $Rep->doUpdate();
        }
    }
}

/**
 * 
 * @return type
 * @throws TRMException
 */
public function actionStart()
{
    $IdsStr = file_get_contents('php://input');
    if( defined("DEBUG") )
    {
        header('Access-Control-Allow-Origin: *');
    }

    if( empty($IdsStr) )
    {
        return;
    }

    //$Rep = new \NewCMS\MapData\NewMapDataObjectRepository($DataSource);
    $Rep = $this->DIC->get(NewMapDataObjectRepository::class);
//    $FieldsType = array();

    $IdsArr = json_decode($IdsStr, true);

    if( key_exists("query", $IdsArr) && !empty($IdsArr["query"]) )
    {
        $Collect = $this->getQuery($IdsArr, $Rep);
        TRMLib::ip($Collect);
    }
    if( key_exists("mutations", $IdsArr) && !empty($IdsArr["mutations"]) )
    {
        $this->postMutations($IdsArr, $Rep);
    }

}

} // NewAjaxQueryController
