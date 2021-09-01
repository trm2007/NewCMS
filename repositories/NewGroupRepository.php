<?php

namespace NewCMS\Repositories;

use NewCMS\Domain\NewGroup;
use NewCMS\Libs\NewHelper;
use NewCMS\Repositories\Exceptions\NewGroupWrongNumberException;
use TRMEngine\DataArray\TRMDataArray;
use TRMEngine\DataMapper\TRMDataMapper;
use TRMEngine\DataObject\Interfaces\TRMDataObjectInterface;
use TRMEngine\DataObject\Interfaces\TRMDataObjectsCollectionInterface;
use TRMEngine\DataSource\Interfaces\TRMDataSourceInterface;
use TRMEngine\DataSource\TRMSqlDataSource;
use TRMEngine\TRMDBObject;

//******************************************************************************
// класс для объекта группа товаров , одно из свойств - ссылка на объект родителя, может быть ноль!!!
//******************************************************************************
class NewGroupRepository extends NewIdTranslitRepository
{
static protected $DataObjectMap = array(
    "group" => array(
        TRMDataMapper::STATE_INDEX => TRMDataMapper::FULL_ACCESS_FIELD,
        TRMDataMapper::FIELDS_INDEX => array(
            "ID_group" => array(
                TRMDataMapper::KEY_INDEX => "PRI",
            ),
        ),
    ),
);

/**
 * @var string - строка с номерами дочерних групп через запятую для использования в запросах
 */
protected $SubGroupsStr = "";
/**
 * @var integer - номер родительской группы,  при выборке методом getAll вернутся дочерние подгруппы
 */
protected $CurrentGroupId = null;
/**
 * @var boolean - флаг, указывающий на необходимость собирать в коллекцию подгруппы подгрупп (рекурсивно)
 */
protected $SubGroupsFlag = false;
/**
 * @var boolean - флаг, указывающий на необходимость собирать всю информацию из родительских групп
 */
protected $FullParentInfoFlag = false;
/**
 *
 * @var bool - если не false, будут выбираться только группы,
 * у кторых present=1
 */
protected $PresentFlag = false;


public function __construct(TRMDataSourceInterface $DataSource)
{
    parent::__construct(NewGroup::class, $DataSource);
}

/**
 * @return boolean - флаг, указывающий на необходимость собирать всю информацию из родительских групп
 */
public function getFullParentInfoFlag()
{
    return $this->FullParentInfoFlag;
}
/**
 * @param boolean $FullParentInfoFlag - флаг, 
 * указывающий на необходимость собирать всю информацию из родительских групп
 */
public function setFullParentInfoFlag($FullParentInfoFlag=true)
{
    $this->FullParentInfoFlag = $FullParentInfoFlag;
}

/**
 * устанавливает условие для флага present при выборке из БД,
 * поумолчанию выбираются все записи не зависимо от значения present,
 * после установки, до первой выборки, будет работать только это значение, 
 * оно обнулится, как и все условия, после выбоки из БД функцией getAll или getOne
 * 
 * @param integer $present - значеине флага
 */
public function setPresentFlagCondition($present = 1)
{
    $this->PresentFlag = true;
    $this->addCondition("group", "GroupPresent", $present);
}



/**
 * задает сортировку коллекции при выборке из БД по дополнительному полю в дополнение к стандвртному набору
 * 
 * @param string $FieldName - имя поля по которому нужно сортировать спислк групп, 
 * если передано одно поле GroupOrder, то порядок его сортировки изменится на новое значение
 * @param boolean $AscFlag - если true - по возрастанию, 0 - по убыванию
 * @param int $FieldQuoteFlag - флаг, указывающий на необходимость заключать сортируемые поля в кавычки
 * 
 * @return void
 */
public function setOrderBy($FieldName = "", $AscFlag = true, $FieldQuoteFlag = TRMSqlDataSource::NEED_QUOTE )
{

    if( $FieldName == "GroupOrder" )
    {
        $this->DataSource->setOrderField( $FieldName, $AscFlag );
        return;
    }
    // очистка значений сортировки
    $this->DataSource->clearOrder();

    if( !empty($FieldName) )
    {
        $this->DataSource->setOrderField( $FieldName, $AscFlag, $FieldQuoteFlag );
    }
    $this->DataSource->setOrderField( "GroupOrder" );
}

/**
 * @return boolean - возвращает флаг, 
 * указывающий на необходимость собирать в коллекцию все из группы из подгрупп, 
 * и далее из подгрупп подгрупп
 */
public function getSubGroupsFlag()
{
    return $this->SubGroupsFlag;
}
/**
 * @param boolean $SubGroupsFlag - флаг, 
 * указывающий на необходимость собирать в коллекцию все из группы из подгрупп, 
 * и далее из подгрупп подгрупп
 */
public function setSubGroupsFlag($SubGroupsFlag=true)
{
    $this->SubGroupsFlag = $SubGroupsFlag;
}

/**
 * устанавливает родительскую группу для коллекции 
 *
 * @param int $id - номер группы (категории) из таблицы БД для товара
 */
public function setCurrentGroupId( $id )
{
    if( $id === null )
    {
        throw new NewGroupWrongNumberException( __METHOD__ );
    }
    $this->CurrentGroupId = intval($id);
}

/**
 * @return integer - текущая родительская группа для коллекции, если задана, либо null
 */
public function getCurrentGroupId()
{
    return $this->CurrentGroupId;
}

/**
 * если установлен SubGroupsFlag, то формируется список всех дочерних групп, и их подгрупп...
 * список включает саму группу CurrentGroupId,
 * иначе только дочерние группы первого уровня 
 * для родительской группы CurrentGroupId, 
 * добавляет условие в SQL-запрос к базе
 */
protected function generateSubGroupStr()
{
    if( $this->CurrentGroupId === null )
    {
        $this->SubGroupsStr = "";
        return;
    }
    if( $this->SubGroupsFlag )
    {
        // проверку empty($IdGroupArray) не делаем, 
        // getAllChildsArray(...) должна вернуть массив хотя бы из одного элемента,
        // так как $this->CurrentGroupId не null !!!
        $IdGroupArray = self::getSubGroupsIdFromDB(
            $this->DataSource->getDBObject(),
            $this->CurrentGroupId,
            $this->PresentFlag, 
            true 
        );
//        NewHelper::getAllChildsArray( 
//                $this->CurrentGroupId, 
//                "group", 
//                "ID_group", 
//                "GroupID_Parent", 
//                "GroupOrder", 
//                $this->PresentFlag ? "GroupPresent" : null,
//                false
//            );
        $this->SubGroupsStr = implode(",", $IdGroupArray->getDataArray());
        if(!empty($this->SubGroupsStr))
        {
            $this->addCondition( "group", "ID_group", $this->SubGroupsStr, "IN" );
        }
    }
    else
    {
        $this->SubGroupsStr = "";
        $this->addCondition( "group", "GroupID_Parent", $this->CurrentGroupId );
    }
}

/**
 * кроме очистки основных параметров запроса
 * так же очищает значение стартовой группы,
 * устанавливает флаг сбора подгрупп в FALSE и очищает строку с подгруппами
 */
public function clearQueryParams()
{
    parent::clearQueryParams();
    $this->CurrentGroupId = null;
    $this->SubGroupsFlag = false;
    $this->SubGroupsStr = "";
    $this->PresentFlag = false;
}

/**
 * получаем данные коллекции из БД, 
 * добавляются условия для сбора коллекции подгрупп по CurrentGroupId
 * 
 * @param TRMDataObjectsCollectionInterface $Collection
 * 
 * @return TRMDataObjectsCollectionInterface
 */
public function getAll( TRMDataObjectsCollectionInterface $Collection = null)
{
    // генерирует и добавляет 
    // либо условие для поиска всех груп, для которых родителем является CurrentGroupId,
    // либо, если установлен SubGroupsFlag, собирает все дочерние группы,
    // а так же подгруппы подгрупп и т.д. и добавляет их в условие
    $this->generateSubGroupStr();

    // получает коллекцию групп из БД
    // после getAll() очищаются все параеметры WHERE запроса
    // так же получает всех родителей, 
    // если установлен FullParentInfoFlag...
    return parent::getAll( $Collection );
}

/**
 * 
 * @param array $DataArray
 * @param TRMDataObjectInterface $DataObject
 * @return NewGroup
 */
protected function getDataObjectFromDataArray(array &$DataArray, TRMDataObjectInterface $DataObject = null)
{
    $NewDataObject = parent::getDataObjectFromDataArray($DataArray, $DataObject);
    
    // если собирать информацию из родительских групп не надо, 
    // то возвращаем объект
    if( !$this->FullParentInfoFlag )
    {
        return $NewDataObject;
    }
    // если полное название группы включает родительскую часть и есть родитель, 
    // то получаем родительскую группу из БД
    if( $NewDataObject["group"]["GroupID_parent"] && $NewDataObject["group"]["ParentGroupTitle"] )
    {
        // в родительском методе TRMIdDataObjectRepository::getById
        // выполнится проверка на наличе объекта с таким Id в контейнере репозитория
        // если объекта с ID не надется, то последует новый запрос к DataSource,
        $NewDataObject->setParentGroupObject( 
                $this->getById( $NewDataObject["group"]["GroupID_parent"] ) 
            );
        $NewDataObject->generateGroupFullTitle();
    }
    return $NewDataObject;
}

/**
 * если у группы поле GroupPresent устанвлено в 0 или FALSE,
 * то после обновления у всех дочерних товаров Present тоже устанавливается в 0,
 * обновляются дочерние товары всех групп из подготовленной коллекции CollectionToUpdate
 * 
 * @param bool $ClearCollectionFlag - если нужно после обновления сохранить коллекцию обновленных объектов, 
 * то этот флаг следует утсановить в false, это может понадобиться дочерним методам,
 * но перед завершением дочернего doUpdate нужно очистить коллекцию,
 * что бы не повторять обновление в будущем 2 раза!
 */
public function doUpdate( $ClearCollectionFlag = true )
{
    parent::doUpdate( false );

    foreach( $this->CollectionToUpdate as $DataObject )
    {
        if( empty($DataObject["group"]["GroupPresent"]) &&
            $CurrentIdGroup = $DataObject["group"]["ID_group"] )
        {
            $query = "UPDATE `table1` SET `present`=0 WHERE `Group`={$CurrentIdGroup}";
            $this->DataSource->executeQuery($query);
        }
    }

    if( $ClearCollectionFlag ) { $this->CollectionToUpdate->clearCollection(); }
}

/**
 * 
 * @param TRMDBObject $DBO
 * @param int $GroupId - ID родительской (стартовой) группы
 * @param boolean $PresentFlag - если указан флаг присутсвия (по умолчанию), будут выбираться только ID,
 * в записи которых поле GroupPresent не пустое
 * @param boolean $AddParenIdFlag - если установлен в true (по умолчанию), то 
 * в результирующий массив будет включен ID родителя ($GroupId)
 * 
 * @return TRMDataArray - возвращает массив с ID дочерних групп и $GroupId,
 * если $AddParenIdFlag === true
 */
public static function getSubGroupsIdFromDB( TRMDBObject $DBO, $GroupId, $PresentFlag = true, $AddParenIdFlag = true)
{
    return NewHelper::getAllChildsArray(
        $DBO,
        $GroupId, 
        "group", 
        "ID_group", 
        "GroupID_Parent", 
        "GroupOrder", 
        $PresentFlag ? "GroupPresent" : null,
        $AddParenIdFlag
    );
}


} // NewGroupRepository