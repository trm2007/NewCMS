<?php

namespace NewCMS\Controllers\AJAX;

use NewCMS\Domain\NewComplexGroup;
use NewCMS\Domain\NewGroup;
use NewCMS\Domain\NewGroupFeature;
use TRMEngine\Cache\TRMCache;
use TRMEngine\DataObject\TRMTypedCollection;
use TRMEngine\Exceptions\TRMException;

/**
 * обработка AJAX-запросов для групп
 */
class NewAjaxGroupController extends NewAjaxCommonController
{

/**
 * возвращает объект NewComplexGroup в виде JSON
 */
public function actionGetComplexGroup()
{
    $GroupId = file_get_contents('php://input');
    
    
    $GroupRep = $this->_RM->getRepository(NewComplexGroup::class); // new NewComplexGroupRepository();
    
    $GroupRep->setFullParentInfoFlag(true);
    
    $ComplexGroup = $GroupRep->getById($GroupId);
    
    $this->renderComplexGroupJSON($ComplexGroup);
}

/**
 * отправляет JSON строку с группой, 
 * если есть, то с родительской группой и ее списком характеристик
 * 
 * @param NewComplexGroup $ComplexGroup
 */
private function renderComplexGroupJSON(NewComplexGroup $ComplexGroup)
{
    $Group = $ComplexGroup->getGroup();
    $Arr = array();
    $ParenGroupId = $Group["group"]["GroupID_parent"];
    /*
     * формирует массив из 2-х JSON-строк,
     * 0-я строка - JSON-объект с характеристиками родительской группы,
     * 1-я строка - JSON-объект самой родительской группы
     */
    if( $ParenGroupId )
    {
        $Arr[0] = json_encode($this->getGroupFeaturesList($ParenGroupId));
        
        $Arr[1] = json_encode(
                $this->_RM->getRepository(NewGroup::class)
                //(new NewGroupRepository())
                ->getById($ParenGroupId)
            );
    }

    echo "[" . json_encode($ComplexGroup);
    if( !empty($Arr) )
    {
        echo ", " . $Arr[0]; //$ParentFeaturesListStr;
        echo ", " . $Arr[1]; //$ParentGroupStr;
    }
    echo "]";
}

/**
 * рендерит клиенту JSON-массив,
 * [0] => пустой объект NewComplexGroup,
 * [1] => null
 * [2] => null
 * 
 */
public function actionGetEmptyComplexGroup()
{
    $ComplexGroup = $this->_RM->getRepository(NewComplexGroup::class)->getNewObject();

    $this->renderComplexGroupJSON($ComplexGroup);
}

/**
 * Служебная вункция, создает объект NewComplexGroup,
 * и заполняет его данными поступившими с клиента в виде JSON
 * 
 * @param bool $NewTranslitFlag - если установлен в true, 
 * то принудительно формирует новый транслит
 * 
 * @return NewComplexGroup
 */
private function getAndInitializeComplexGroupFromJSON($NewTranslitFlag = false)
{
    $json = file_get_contents('php://input');

    $ComplexGroup = new NewComplexGroup();

    // инициализируем объект из массива, полученного из JSON
    $ComplexGroup->initializeFromArray( json_decode($json, true) );

    if( $ComplexGroup->getGroup()->getData("group", "ParentGroupTitle") )
    {
        $NewGroupRep = $this->_RM->getRepository(NewGroup::class); // new NewGroupRepository();
        $NewGroupRep->setFullParentInfoFlag(true);
        $Parent = $NewGroupRep->getById( $ComplexGroup->getGroup()->getData("group", "GroupID_parent") );
        $ComplexGroup->getGroup()->setParentGroupObject($Parent);
    }
    $Translit = $ComplexGroup->getGroup()->getTranslit();
    if( empty($Translit) || $NewTranslitFlag )
    {
        $ComplexGroup->getGroup()->translit();
    }
    
    return $ComplexGroup;
}

/**
 * сохраняет NewComplexGroup в БД
 */
public function actionUpdateComplexGroup()
{
    $ComplexGroup = $this->getAndInitializeComplexGroupFromJSON();

    $rep = $this->_RM->getRepository(NewComplexGroup::class); // new NewComplexGroupRepository();

    $rep->update($ComplexGroup);
    $rep->doUpdate();
    $MyCache = $this->DIC->get(TRMCache::class);
    $MyCache->clearCache("catalogmenu0");
    $MyCache->clearCache("catalogmenu" . \GlobalConfig::$ConfigArray["StartGroup"]);
    $MyCache->clearCache("catalogmenu" . \GlobalConfig::$ConfigArray["GlobalStartGroup"]);

    $this->renderComplexGroupJSON($ComplexGroup);
}

/**
 * сохраняет NewComplexGroup в БД
 */
public function actionSaveAsNewComplexGroup()
{
    $ComplexGroup = $this->getAndInitializeComplexGroupFromJSON(true);
    $ComplexGroup->resetId();

    $rep = $this->_RM->getRepository(NewComplexGroup::class); // new NewComplexGroupRepository();

    $rep->insert($ComplexGroup);
    $rep->doInsert();
    $MyCache = $this->DIC->get(TRMCache::class);
    $MyCache->clearCache("catalogmenu0");

    $this->renderComplexGroupJSON($ComplexGroup);
}

/**
 * возвращает список характеристик для группы,
 * должен быть передан ID-группы
 */
public function actionGetGroupFeaturesList()
{
    $GroupId = file_get_contents('php://input');
    
    $FeaturesList = $this->getGroupFeaturesList($GroupId);
    
    echo json_encode($FeaturesList);
}
/**
 * возвращает коллекцию характеристик NewGroupFeature для указанного продукта
 * 
 * @param int $GroupId - ID-продукта, для которого нужно получить характеристики
 * 
 * @return TRMTypedCollection
 */
private function getGroupFeaturesList($GroupId)
{
    return $this->_RM->getRepository(NewGroupFeature::class)->getBy("groupfeature", "ID_Group", $GroupId);
}

/**
 * копирует список отсутсвующих характеристик из родительской группы в товар
 */
public function actionCopyFeaturesFromOtherGroup()
{
    $GroupIdsJSONId = file_get_contents('php://input');
    $Arr = json_decode($GroupIdsJSONId, true);
    $GroupId = $Arr[1];
    $OtherGroupId = $Arr[0];

    $GroupFeaturesRep = $this->_RM->getRepository(NewGroupFeature::class); // new NewGroupFeatureRepository();
    
    $GroupFeaturesCollection = $GroupFeaturesRep->getBy( "groupfeature", "ID_Group", $GroupId );
    $OtherGroupFeaturesCollection = $GroupFeaturesRep->getBy("groupfeature", "ID_Group", $OtherGroupId);

    if( !$OtherGroupFeaturesCollection )
    {
        throw new TRMException("Нет характеристик у родительской группы - " . $OtherGroupId);
    }
    if( !$GroupFeaturesCollection )
    {
        $GroupFeaturesCollection = new TRMTypedCollection(NewGroupFeature::class);
    }
    
    foreach( $OtherGroupFeaturesCollection as $OtherGroupFeature )
    {
        $EnadleFlag = false;
        foreach($GroupFeaturesCollection as $GroupFeature )
        {
            if( $GroupFeature->getData("features", "ID_Feature") 
                == $OtherGroupFeature->getData("features", "ID_Feature") 
            )
            {
                $EnadleFlag = true;
                break;
            }
        }
        // если характеристика есть и в той и в другой коллекции,
        if( $EnadleFlag ) 
        {
            // при этом она не установлена в дочерней группе,
            // то копируем в нее характеристику из родительской (другой) группы,
            // цикл прерван, поэтому в $GroupFeature данные с найденной характеристикой
            // для дочеренй группы 
            if( !$GroupFeature->getData("goodsfeatures", "FeatureValue") )
            {
                $GroupFeatureArr = $OtherGroupFeature->getRow("groupfeature");
                $GroupFeatureArr["ID_Group"] = $GroupId;
                $GroupFeature->setRow( "groupfeature", $GroupFeatureArr );
            }
            // продолжаем цикл не создавая новую характеристику в коллекции для продукта
            continue;
        }

        // если характеристики из родительской (другой) группы 
        // нет в коллекции для дочеренй группы, то копируем
        $TmpGroupFeature = new NewGroupFeature();
        $TmpGroupFeature->setRow( "features", $OtherGroupFeature->getRow("features") );
        
        $GroupFeatureArr = $OtherGroupFeature->getRow("groupfeature");
        $GroupFeatureArr["ID_Group"] = $GroupId;
        
        $TmpGroupFeature->setRow( "groupfeature", $GroupFeatureArr );
        $GroupFeaturesCollection->addDataObject($TmpGroupFeature);
    }
    
    echo json_encode( $GroupFeaturesCollection );
}


} // NewAjaxGroupController
