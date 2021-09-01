<?php

namespace NewCMS\Repositories;

use NewCMS\Domain\Exceptions\NewComplectZeroPartPriceException;
use NewCMS\Domain\NewLiteProduct;
use NewCMS\Libs\NewHelper;
use TRMEngine\DataArray\TRMDataArray;
use TRMEngine\DataMapper\TRMDataMapper;
use TRMEngine\DataMapper\TRMSafetyFields;
use TRMEngine\DataObject\Interfaces\TRMDataObjectInterface;
use TRMEngine\DataObject\Interfaces\TRMDataObjectsCollectionInterface;
use TRMEngine\DataSource\Interfaces\TRMDataSourceInterface;
use TRMEngine\TRMDBObject;

/**
 * с 2018.07.28 - основной класс хранилища для работы с продуктом из таблицы table1 без вспомогательных объектов,
 * но с описанием из отдельной таблицы - goodsdescription и единицами измерения - unit
 *
 * @author TRM
 */
class NewLiteProductRepository extends NewIdTranslitRepository
{
static protected $DataObjectMap = array(
    "table1" => array(
        TRMDataMapper::STATE_INDEX => TRMDataMapper::FULL_ACCESS_FIELD,
        TRMDataMapper::FIELDS_INDEX => array(
            "ID_price" => array(
                TRMDataMapper::KEY_INDEX => "PRI",
            ),
            "unit" => array(
                TRMDataMapper::RELATION_INDEX => array( TRMDataMapper::OBJECT_NAME_INDEX => "unit", 
                                                           TRMDataMapper::FIELD_NAME_INDEX => "ID_unit" ), // из unit будет выбрана запись (только одна запись из unit для каждой из table1 так как ID_unit - уникален!!!), для которой `unit`.`ID_unit` === `table1`.`unit`
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
    "goodsdescription" => array(
        TRMDataMapper::STATE_INDEX => TRMDataMapper::READ_ONLY_FIELD,
        TRMDataMapper::FIELDS_INDEX => array(
            "ID_goods" => array(
                TRMDataMapper::KEY_INDEX => "PRI",
                TRMDataMapper::RELATION_INDEX => array( TRMDataMapper::OBJECT_NAME_INDEX => "table1", 
                                                           TRMDataMapper::FIELD_NAME_INDEX => "ID_price" ),
            ),
            "GoodsDescription" => array(
                // у поля приоритет над State для всей таблицы
                TRMDataMapper::STATE_INDEX => TRMDataMapper::FULL_ACCESS_FIELD,
                TRMDataMapper::TYPE_INDEX => "text",
            ),
        ),
    ),
);


public function __construct(TRMDataSourceInterface $DataSource)
{
    parent::__construct(NewLiteProduct::class, $DataSource);

    $this->DataMapper->removeField( "table1" , "Description" );
    $this->DataMapper->removeField( "goodsdescription", "GoodsImage" );
    $this->DataMapper->setFieldState( "goodsdescription", "GoodsDescription", TRMSafetyFields::FULL_ACCESS_FIELD );
}

public function update(TRMDataObjectInterface $DataObject)
{
    $DataObject->setData("table1", "ModifyDate", time() );
    parent::update($DataObject);
}

public function updateCollection(TRMDataObjectsCollectionInterface $Collection)
{
    foreach( $Collection as $DataObject )
    {
        $DataObject->setData("table1", "ModifyDate", time() );
    }
    
    parent::updateCollection($Collection);
}

/**
 * считает цену товара в рублях и с наценками
 * {@inheritDoc} 
 */
public function getOne(TRMDataObjectInterface $DataObject = null)
{
    $LiteProduct = parent::getOne($DataObject);
    if( !$LiteProduct ) { return null; }

    static::calculateProductPrice($this->DataSource->getDBObject(), $LiteProduct);
    
    return $LiteProduct;
}

/**
 * считает цену для каждого товара в рублях и с наценками
 * {@inheritDoc} 
 */
public function getAll(TRMDataObjectsCollectionInterface $Collection = null)
{
    $LiteProductsCollection = parent::getAll($Collection);
    if( !$LiteProductsCollection ) { return null; }

    foreach( $LiteProductsCollection as $LiteProduct )
    {
        static::calculateProductPrice($this->DataSource->getDBObject(), $LiteProduct);
    }
    
    return $LiteProductsCollection;
}

/**
 * считает суммарную цену товара, 
 * если это комплект, то вызывает рекурсивную функцию NewHelper::recursivePrice, 
 * если составляющие комплекта, в свою очередь, тоже являются комплектами,
 * то будет вызываться и для них
 * 
 * @param TRMDBObject $DBO
 * @param NewLiteProduct $LiteProduct - продукт, для которого вычмсляется базовая цена,
 * если комплект, то суммируются все цены составляющих, 
 * и наценки
 */
static protected function calculateProductPrice(TRMDBObject $DBO, NewLiteProduct $LiteProduct)
{
    try
    {
        $ComplectPrice0 = NewHelper::recursivePrice( $DBO, array(
            "ID_Price" => $LiteProduct->getId(),
            "price0" => $LiteProduct->getData("table1", "price0"),
            "valuta" => $LiteProduct->getData("table1", "valuta")) );
    }
    catch(NewComplectZeroPartPriceException $e)
    {
        $ComplectPrice0 = 0;
    }
    $LiteProduct->setData("table1", "PriceRUB", $ComplectPrice0);
    NewHelper::setPrices($LiteProduct, $ComplectPrice0);
}

/**
 * @param TRMDBObject $DBO
 * @param int $GroupId
 * @param boolean $SubGroupFlag
 * @param boolean $PresentFlag
 * 
 * @return TRMDataArray - возвращает массив с ID товаров, содержащихся в дочерних групп 
 * и самой родительсокй $GroupId, если $AddParenIdFlag === true
 */
public static function getProductsIdFromDB( TRMDBObject $DBO, $GroupId, $SubGroupFlag = false, $PresentFlag = true )
{
    // проверка выбраны ли все дочерние подгруппы
    if( $SubGroupFlag )
    {
        $SubGroupsStr = implode(
            ", ", 
            NewGroupRepository::getSubGroupsIdFromDB($DBO, $GroupId, $SubGroupFlag)->getDataArray() );
    }
    else { $SubGroupsStr = (string)$GroupId; }
    $query = "SELECT `ID_price` FROM `table1`"
            . " WHERE `table1`.`Group` IN ({$SubGroupsStr})";
    if( $PresentFlag )
    {
        $query .= " AND `table1`.`present`=1";
    }
    
    $ResArr = new TRMDataArray();

    $result = $DBO->query($query);
    if( !$result ) { return $ResArr; }

    while( $row = $result->fetch_array(MYSQLI_ASSOC) )
    {
        $ResArr[] = $row["ID_price"];
    }
    
    return $ResArr;
}


} // NewLiteProductRepository