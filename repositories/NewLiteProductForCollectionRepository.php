<?php

namespace NewCMS\Repositories;

use NewCMS\Domain\NewLiteProductForCollection;
use NewCMS\Repositories\Exceptions\NewGroupWrongNumberException;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;
use TRMEngine\DataMapper\TRMDataMapper;
use TRMEngine\DataObject\Interfaces\TRMDataObjectsCollectionInterface;
use TRMEngine\DataSource\Interfaces\TRMDataSourceInterface;
use TRMEngine\DataSource\TRMSqlDataSource;
use TRMEngine\Exceptions\TRMSqlQueryException;

/**
 * с 2018.07.15 - основной класс для работы с хранилищем коллекции товаров
 *
 * @version 2019.03.24
 */
class NewLiteProductForCollectionRepository extends NewLiteProductRepository
{
static protected $DataObjectMap = array(
    "table1" => array(
        TRMDataMapper::STATE_INDEX => TRMDataMapper::FULL_ACCESS_FIELD,
        TRMDataMapper::FIELDS_INDEX => array(
            "ID_price" => array(
                TRMDataMapper::KEY_INDEX => "PRI",
            ),
            "unit" => array(
                // из unit будет выбрана запись 
                // (только одна запись из unit для каждой из table1 так как ID_unit - уникален!!!), 
                // для которой `unit`.`ID_unit` === `table1`.`unit`
                TRMDataMapper::RELATION_INDEX => array( TRMDataMapper::OBJECT_NAME_INDEX => "unit", 
                                                        TRMDataMapper::FIELD_NAME_INDEX => "ID_unit" ), 
            ),
            "vendor" => array(
                TRMDataMapper::RELATION_INDEX => array( TRMDataMapper::OBJECT_NAME_INDEX => "vendors", 
                                                        TRMDataMapper::FIELD_NAME_INDEX => "ID_vendor" ),
            ),
        )
    ),
    "unit" => array(
        TRMDataMapper::STATE_INDEX => TRMDataMapper::READ_ONLY_FIELD,
        TRMDataMapper::FIELDS_INDEX => array(
            "ID_unit" => array(
                TRMDataMapper::KEY_INDEX => "PRI",
            ),
        ),
    ),
    "vendors" => array(
        TRMDataMapper::STATE_INDEX => TRMDataMapper::READ_ONLY_FIELD,
        TRMDataMapper::FIELDS_INDEX => array(
            "ID_vendor" => array(
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
 * @var string - строка с номерами товаров (ID_price) среди которых производится выборка
 */
protected $IdPriceStr = "";
/**
 * @var array - массив характеристик, которым должен удовлетворять список
 */
protected $FeaturesList = array();
/**
 * @var integer - номер стартовой группы для всех товаров и подкатегорий, в том числе для выборки по характеристикам
 */
protected $CurrentGroupId = null;
/**
 * @var boolean - флаг, указывающий на необходимость собирать в коллекцию товаров из подгрупп
 */
protected $SubGroupsFlag = true;


public function __construct(TRMDataSourceInterface $DataSource) 
{
    NewRepository::__construct( NewLiteProductForCollection::class, $DataSource );

    $this->DataMapper->removeField( "table1", "Description" );

    $this->setOrderBy();
}

public function clearCondition() {
    parent::clearCondition();
    $this->IdPriceStr = "";
    $this->CurrentGroupId = null;
    $this->FeaturesList = array();
    $this->SubGroupsStr = "";
}

/**
 * устанавливает условие для флага present при выборке из БД,
 * поумолчанию выбираются все записи не зависимо от значения present,
 * после установки, до первой выборки, будет работать только это значение, 
 * оно обнулится, как и все условия, после выбоки из БД функцией getAll или getOne
 * 
 * @param integer $present - значение флага 1 или 0
 */
public function setPresentFlagCondition($present = 1)
{
    $this->addCondition("table1", "present", $present);
}

/**
 * задает сортировку коллекции при выборке из БД по дополнительному полю в дополнение к стандвртному набору
 * 
 * @param string $FieldName - имя поля по которому нужно сортировать, 
 * если не задано, то в сортировку добавляется только стандартный набор полей 
 * ( (CASE WHEN `price0` =0 THEN 1 ELSE 0 END),  [$FieldName  ,] item_order, price0, Group, Name ),
 * если передано одно из стандартных полей, то порядок его сортировки изменится на новое значение
 * 
 * @param boolean $AscFlag - если true - по возрастанию, 0 - по убыванию
 */
public function setOrderBy($FieldName = "", $AscFlag = true, $FieldQuoteFlag = TRMSqlDataSource::NEED_QUOTE )
{

    if( $FieldName == "Group" || $FieldName == "item_order" )
    {
        $this->DataSource->setOrderField( $FieldName, $AscFlag );
        return;
    }
    // очистка значений сортировки
    $this->DataSource->clearOrder();
    // товары без цен всегда отображаются в конце
    // у комплектов все цены нулевые, поэтому пока комментируем
//    $this->DataSource->setOrderField(
//        "(CASE WHEN `price0` =0 THEN 1 ELSE 0 END)", 
//        true, 
//        TRMSqlDataSource::NOQUOTE
//    );

    if( !empty($FieldName) )
    {
        $this->DataSource->setOrderField( $FieldName, $AscFlag, $FieldQuoteFlag );
    }
    $this->DataSource->setOrderField( "Group" );
    $this->DataSource->setOrderField( "item_order" );
}

/**
 * @return boolean - возвращает флаг, указывающий на необходимость собирать в коллекцию товары из подгрупп
 */
public function getSubGroupsFlag()
{
    return $this->SubGroupsFlag;
}
/**
 * @param boolean $SubGroupsFlag - флаг, указывающий на необходимость собирать в коллекцию товары из подгрупп
 */
public function setSubGroupsFlag($SubGroupsFlag=true)
{
    $this->SubGroupsFlag = $SubGroupsFlag;
}

/**
 * устанавливает группу начиная с которой собираем список товаров, 
 * так же формируется список всех дочерних групп, и новый SQL-запрос к базе
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
 * @return integer - текущая группа для коллекции товаров, если задана, либо null
 */
public function getCurrentGroupId()
{
    return $this->CurrentGroupId;
}

/**
 * формируется список всех дочерних групп, добавляет условие в SQL-запрос к базе
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
        // getSubGroupsIdFromDB(...) должна вернуть массив хотя бы из одного элемента,
        // так как $this->CurrentGroupId не null !!!
        $IdGroupArray = NewGroupRepository::getSubGroupsIdFromDB(
                $this->DataSource->getDBObject(),
                $this->CurrentGroupId, 
                true
            );
        $this->SubGroupsStr = implode(",", $IdGroupArray->getDataArray() );
    }
    else { $this->SubGroupsStr = (string)$this->CurrentGroupId; }

    $this->addCondition("table1", "Group", $this->SubGroupsStr, "IN");
}

/**
 * устанавливается массив со списком характеристик
 *
 * @param array $FeaturesList - двумерный массив характеристик из array( 0 => array( "id" , "value" ), 1 => array(...), ... )
 */
public function setFeaturesList( array $FeaturesList=null )
{
    if( !isset($FeaturesList) || empty($FeaturesList) )
    {
        $this->FeaturesList = array();
        $this->IdPriceStr = '';
        return true;
    }
    $this->FeaturesList = $FeaturesList;
}

/**
 * @return array - возвращает двумерный массив характеристик из array( "id" , "value" )
 */
public function getFeaturesList()
{
    return $this->FeaturesList;
}

/**
 * добавляет условие для SQL-запроса 
 * со списком номеров товаров удовлетворяющих заданным характеристикам, 
 *
 * @return integer - возвращает количество полученных ID-товаров удовлетворяющих установленным характеристикам, 
 * 0 - если ничего не нашлось
 */
protected function generateFeaturesSQL()
{
    $FeaturesList = $this->FeaturesList;
    $this->IdPriceStr = "";
    if( empty( $FeaturesList ) ) { return 0; }

    reset($FeaturesList);
    $cur0 = current($FeaturesList);
    $OldIdFeatures = intval(  $cur0["id"]  );
    $firstflag = true;

    $k=0;
    $query = "SELECT `ID_Price` FROM `goodsfeatures` ";

    // проходим по списку характеристик, 
    // если для одной характеристики (с одним ID) установлено несколько значений, объединяем их через OR
    // разные характеристики объединяются через AND
    foreach($FeaturesList as $cur )
    {
            $featuresid = addcslashes( intval($cur["id"]), "'");
            $featuresval = addcslashes( $cur["value"], "'");

            if( $firstflag ){ $query .= " WHERE ("; $firstflag = false; } // если это самый первый вход в цикл, то продолжаем дальше, добавится просто условие в скобках
            elseif( $OldIdFeatures == $featuresid ){  $query .= " OR "; } // если старое значение ID_Features равно новому, то выбираем значение комбинацией ИЛИ
            elseif( $OldIdFeatures != $featuresid ) // если новая характеристика, то открываем новую выборку
            {
                // закрываем скобку отрытую в WHERE и продложаем следующую выборку по характеристикам
                $query .= ") AND `ID_Price` IN (SELECT `ID_Price` FROM `goodsfeatures` WHERE ("; 
                // k - счетчик отрытых скобок для дополнительных выборок
                $k++; 
            }

            $OldIdFeatures = intval($cur["id"]);

            $query .= "( `FeaturesValue` = '{$featuresval}'";
            $query .= " AND `ID_Feature` = '{$featuresid}' )";
    }
    $query .= ")";

    for(;$k>0;$k--) { $query .= " ) "; } // закрываем все скобки в запросе, в зависимости от количества открытых ранее

    $query .= " GROUP BY  `ID_Price`  ";

    $result = $this->DataSource->executeQuery($query);

    if(!$result)
    {
        throw new TRMSqlQueryException( $query );
    }
    if( !$result->num_rows ) { return 0; }
    $IdPriceArray = $this->DataSource->getDBObject()->fetchAll($result, MYSQLI_NUM);

    // из многомерного массива, который вернет fetch_all делаем одномерный, 
    // перегоняя в промежутке объект массива в итератор и обратно в массив, но уже одномерный
    $iterator = new RecursiveIteratorIterator(new RecursiveArrayIterator($IdPriceArray));

    $this->IdPriceStr = trim(implode(",", iterator_to_array($iterator, false)));

    if( !empty($this->IdPriceStr) )
    {
        $this->addCondition("table1", "ID_price", $this->IdPriceStr, "IN");
    }

    return $result->num_rows;
}

/**
 * @return boolean - если товаров с заданными характеристиками не удалось найти и в случае ошибки вернет false
 */
protected function generateSubStrings()
{
    if( empty($this->SubGroupsStr) ) { $this->generateSubGroupStr(); }
    if( !empty( $this->FeaturesList ) && empty($this->IdPriceStr) )
    {
        if( !$this->generateFeaturesSQL() ) { return false; }
    }
    return true;
}

/**
 * получаем данные коллекции из БД
 * 
 * @param TRMDataObjectsCollectionInterface $Collection
 * 
 * @return TRMDataObjectsCollectionInterface
 */
public function getAll( TRMDataObjectsCollectionInterface $Collection = null)
{
    if( !$this->generateSubStrings() )
    {
        return null;
    }
    // получает коллекцию товаров из БД
    // после getAll() очищаются все параеметры WHERE запроса
    // так же получает все цены для товаров, если они являются комплектом, то рекурсивно...
    return parent::getAll( $Collection );
}

/**
 * получаем количество записей удовлетворяющих запросу из БД
 *
 * @return int|boolean
 */
public function getProductsCount()
{
    if( !$this->generateSubStrings() )
    {
        return 0;
    }

    $CountQuery = preg_replace( 
        "/^SELECT(.+)FROM(.*)(?:ORDER.*|LIMIT.*|OFFSET.*)$/iU" , "SELECT count(`ID_price`) FROM$2", 
        $this->DataSource->makeSelectQuery( $this->DataMapper ) 
    );

    $result = $this->DataSource->executeQuery($CountQuery);
    if(!$result)
    {
        throw new TRMSqlQueryException($CountQuery);
    }
    $count = $result->fetch_array(MYSQLI_NUM)[0];

    return $count;
}

/**
 * @return int - возвращает общее кол-во записей в таблице
 */
public function getTotalCount()
{
    $Row = $this->DataSource->
            executeQuery("SELECT count(`ID_price`) FROM `table1` WHERE `present`=1")->
            fetch_row();
    return $Row[0];
}


} // NewLiteProductForCollectionRepository