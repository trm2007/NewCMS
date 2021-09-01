<?php

namespace NewCMS\Repositories;

use NewCMS\Domain\Exceptions\NewComplectZeroPartPriceException;
use NewCMS\Domain\NewComplectPart;
use NewCMS\Libs\NewHelper;
use TRMEngine\DataMapper\TRMDataMapper;
use TRMEngine\DataObject\Interfaces\TRMDataObjectInterface;
use TRMEngine\DataObject\Interfaces\TRMDataObjectsCollectionInterface;
use TRMEngine\DataSource\Interfaces\TRMDataSourceInterface;
use TRMEngine\TRMDBObject;

/**
 * Тестируем новый DataMapper + Repository
 *
 * @author TRM 2018-08-26
 */
class NewComplectPartRepository extends NewParentedRepository
{
static protected $DataObjectMap = array(
    "complect" => array(
        TRMDataMapper::STATE_INDEX => TRMDataMapper::FULL_ACCESS_FIELD,
        TRMDataMapper::FIELDS_INDEX => array(
            "ID_Complect" => array(
                TRMDataMapper::KEY_INDEX => "PRI",
            ),
            "ID_Price" => array(
                TRMDataMapper::KEY_INDEX => "PRI",
                TRMDataMapper::RELATION_INDEX => array( 
                    TRMDataMapper::OBJECT_NAME_INDEX => "table1", 
                    TRMDataMapper::FIELD_NAME_INDEX => "ID_price"
                ), // из таблицы table1 будут выбраны все записи, значение поля `table1`.`ID_price` которых содержится в списке `complect`.`ID_Price`
            ),
        ),
    ),
    "table1" => array( // tn
        TRMDataMapper::STATE_INDEX => TRMDataMapper::READ_ONLY_FIELD,
        TRMDataMapper::FIELDS_INDEX => array(
            "ID_price" => array(
                TRMDataMapper::KEY_INDEX => "PRI",
            ),
            "Name" => array(
                TRMDataMapper::TYPE_INDEX => "varchar(1000)",
            ),
            "unit" => array(
                TRMDataMapper::RELATION_INDEX => array( 
                    TRMDataMapper::OBJECT_NAME_INDEX => "unit", 
                    TRMDataMapper::FIELD_NAME_INDEX => "ID_unit" ), // из unit будет выбрана запись (только одна запись из unit для каждой из table1 так как ID_unit - уникален!!!), для которой `unit`.`ID_unit` === `table1`.`unit`
            ),
            "vendor" => array(
                TRMDataMapper::RELATION_INDEX => array( 
                    TRMDataMapper::OBJECT_NAME_INDEX => "vendors", 
                    TRMDataMapper::FIELD_NAME_INDEX => "ID_vendor" ), // из unit будет выбрана запись (только одна запись из unit для каждой из table1 так как ID_unit - уникален!!!), для которой `unit`.`ID_unit` === `table1`.`unit`
            ),
        )
    ),
    "unit" => array(
        TRMDataMapper::STATE_INDEX => TRMDataMapper::READ_ONLY_FIELD,
        TRMDataMapper::FIELDS_INDEX => array(
            "ID_unit" => array(
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


public function __construct(TRMDataSourceInterface $DataSource) 
{
    parent::__construct(NewComplectPart::class, $DataSource);

    $this->DataMapper->setParentIdFieldName(array( "complect", "ID_Complect" ));
    $this->DataMapper->removeField( "table1", "Description" );

    $this->setOrder();
}

/**
 * задает сортировку коллекции при выборке из БД по стандартному набору
 * ( (CASE WHEN `price0` =0 THEN 1 ELSE 0 END), item_order, price0, Group, Name ),
 */
public function setOrder()
{
    $this->DataSource->setOrder( array( "(CASE WHEN `price0` =0 THEN 1 ELSE 0 END)" => "ASC",
                                        "item_order" => "ASC",
                                        "price0" => "ASC",
                                        "`Group`" => "ASC",
                                        "Name" => "ASC" ) );
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
 * @param NewComplectPart $LiteProduct - продукт, для которого вычмсляется базовая цена,
 * если комплект, то суммируются все цены составляющих, 
 * и наценки
 */
static protected function calculateProductPrice(TRMDBObject $DBO, NewComplectPart $LiteProduct)
{
    try
    {
        $ComplectPrice0 = NewHelper::recursivePrice( 
            $DBO,
            array(
                "ID_Price" => $LiteProduct->getData("table1", "ID_price"),
                "price0" => $LiteProduct->getData("table1", "price0"),
                "valuta" => $LiteProduct->getData("table1", "valuta") 
            )
        );
    }
    catch(NewComplectZeroPartPriceException $e)
    {
        $ComplectPrice0 = 0;
    }
    $LiteProduct->setData("table1", "PriceRUB", $ComplectPrice0);
    NewHelper::setPrices($LiteProduct, $ComplectPrice0);
}


} // NewComplectCollectionRepository