<?php

namespace NewCMS\Controllers\AJAX;

use NewCMS\Domain\NewGroup;
use NewCMS\Widgets\NewFeaturesSelector;
use TRMEngine\Cache\TRMCache;

/**
 * обработка AJAX-запросов для получения дерева каталога
 * и других меню
 */
class NewAjaxMenuJSONController extends NewAjaxCommonController
{

/**
 * формирует JSON-дерево групп!!!
 */
public function actionGetGroupTree()
{
    $JSONArr = json_decode( file_get_contents('php://input'), true );

    if( isset($JSONArr["ID"]) && $JSONArr["ID"] !== false && $JSONArr["ID"] !== "" )
    {
        $StartId = $JSONArr["ID"];
    }
    else
    {
        $StartId = 0; //\GlobalConfig::$ConfigArray["StartGroup"];
    }

    $MyCache = $this->DIC->get(TRMCache::class);
    $CatalogMenuContent = $MyCache->getCache("catalogmenu" . $StartId);
    if($CatalogMenuContent)
    {
        echo $CatalogMenuContent;
        return;
    }

    $GroupCollectionRepository = $this->_RM->getRepository(NewGroup::class);

    $GroupCollectionRepository->getDataMapper()->removeField("group", "GroupImage");
    $GroupCollectionRepository->getDataMapper()->removeField("group", "GroupComment");
    $GroupCollectionRepository->getDataMapper()->removeField("group", "GroupBigImage");
    $GroupCollectionRepository->getDataMapper()->removeField("group", "GroupVisits");
    $GroupCollectionRepository->setOrderBy("GroupOrder", true);
    $GroupCollectionRepository->setCurrentGroupId($StartId);
    $GroupCollectionRepository->setSubGroupsFlag();
    if( isset($JSONArr["Present"]) && $JSONArr["Present"] )
    {
        $GroupCollectionRepository->setPresentFlagCondition();
    }
    $GroupCollection = $GroupCollectionRepository->getAll();

    $ParentIdFieldName = "GroupID_parent";

    $TotalArray = $GroupCollection->getTotalArray();

    $rows = array();
    foreach($TotalArray as $CurrentGroup)
    {
        $rows[ $CurrentGroup["group"]["ID_group"] ] = $CurrentGroup["group"];
    }

    foreach( $rows as $key => $val )
    {
        if( isset($val[$ParentIdFieldName]) )
        {
//            if( isset ( $rows[ $val[$ParentIdFieldName ] ] ) )
            {
                $rows[ $val[$ParentIdFieldName] ]['children'][] = &$rows[$key];
            }
        }
    }
    $rows = &$rows[$StartId];
    unset($rows[$StartId]);
    $rows = &$rows["children"];
    unset($rows["children"]);
//header("Content-Type: text/html; charset=UTF-8");
//\TRMEngine\Helpers\TRMLib::ap($rows);exit;

    $CatalogMenuContent = json_encode( $rows, JSON_PRESERVE_ZERO_FRACTION | JSON_UNESCAPED_UNICODE );
    $MyCache->setCache("catalogmenu" . $StartId, $CatalogMenuContent);

    echo $CatalogMenuContent;
}

/**
 * формирует список характеристик для товаров в группе,
 * если так же передан URL, разбирает его,
 * и на основании URL помечает выбранные характеристики
 */
public function actionGetFeaturesList()
{
    $JSONArr = json_decode( file_get_contents('php://input'), true );

    $FeaturesSelector = new NewFeaturesSelector($this->getDBObject()); // ($Group["group"]["ID_group"]);

    if( isset($JSONArr["GroupId"]) && $JSONArr["GroupId"] !== false && $JSONArr["GroupId"] !== "" )
    {
        $GroupId = $JSONArr["GroupId"];
    }
    else
    {
        $GroupId = \GlobalConfig::$ConfigArray["StartGroup"];
    }
    if( isset($JSONArr["URL"]) && $JSONArr["URL"] !== false && $JSONArr["URL"]!== "" )
    {
        $URL = $JSONArr["URL"];
        $FeaturesSelector->selectFeaturesFromURL($URL);
    }
    $FeaturesSelector->setCurrentGroupId($GroupId, "");
    $FeaturesSelector->generateFeaturesValsArray();
    
    echo json_encode(array(
        $FeaturesSelector->FeaturesValsArray,
        $FeaturesSelector->SelectedFeaturesList
        ), JSON_PRESERVE_ZERO_FRACTION | JSON_UNESCAPED_UNICODE);
}


} // NewAjaxMenuJSONController
