<?php

namespace NewCMS\Repositories;

use NewCMS\Domain\NewOrderProduct;
use TRMEngine\DataMapper\TRMDataMapper;
use TRMEngine\DataObject\TRMDataObjectsCollection;
use TRMEngine\DataSource\Interfaces\TRMDataSourceInterface;
use TRMEngine\DataSource\TRMSqlDataSource;

/**
 * класс для хранилища товаров заказа, зависит (принадлежит) заказу - NewComplexOrder
 */
class NewOrderProductRepository extends NewParentedRepository
{
static protected $DataObjectMap = array(
    "new_order_products" => array(
        TRMDataMapper::STATE_INDEX => TRMDataMapper::FULL_ACCESS_FIELD,
        TRMDataMapper::FIELDS_INDEX => array(
            "id_product" => array(
                TRMDataMapper::KEY_INDEX => "PRI",
                TRMDataMapper::RELATION_INDEX => array( 
                    TRMDataMapper::OBJECT_NAME_INDEX => "table1", 
                    TRMDataMapper::FIELD_NAME_INDEX => "ID_price" 
                ), // из таблицы table1 будут выбраны все записи, значение поля `table1`.`ID_price` которых содержится в списке `complect`.`ID_Price`
            ),
            "id_order" => array(
                TRMDataMapper::KEY_INDEX => "PRI",
            ),
        ),
    ),
    "table1" => array(
        TRMDataMapper::STATE_INDEX => TRMDataMapper::READ_ONLY_FIELD,
        TRMDataMapper::FIELDS_INDEX => array(
            "ID_price" => array(
                TRMDataMapper::KEY_INDEX => "PRI",
            ),
            "unit" => array(
                TRMDataMapper::RELATION_INDEX => array( 
                    TRMDataMapper::OBJECT_NAME_INDEX => "unit", 
                    TRMDataMapper::FIELD_NAME_INDEX => "ID_unit" 
                ),
            ),
        )
    )
);


public function __construct(TRMDataSourceInterface $DataSource)
{
    parent::__construct(NewOrderProduct::class, $DataSource);

    $this->DataMapper->setParentIdFieldName(array( "new_order_products", "id_order" ));
    $this->DataMapper->removeField("table1", "Description");
    $OrderFields = array(
        "new_orders" => array(
            TRMDataMapper::STATE_INDEX => TRMDataMapper::READ_ONLY_FIELD,
            TRMDataMapper::FIELDS_INDEX => array(
                "id" => array(
                    TRMDataMapper::KEY_INDEX => "PRI",
                    TRMDataMapper::RELATION_INDEX => array( 
                        TRMDataMapper::OBJECT_NAME_INDEX => "new_order_products", 
                        TRMDataMapper::FIELD_NAME_INDEX => "id_order" 
                    ),
                ),
                "date" => array()
            )
        ),
    );
    $this->DataMapper->setFieldsArrayFor( 
        "new_orders", 
        $OrderFields["new_orders"][TRMDataMapper::FIELDS_INDEX],
        TRMDataMapper::READ_ONLY_FIELD
    );
    $UnitFields = array(
        "unit" => array(
            TRMDataMapper::STATE_INDEX => TRMDataMapper::READ_ONLY_FIELD,
            TRMDataMapper::FIELDS_INDEX => array(
                "ID_unit" => array(
                    TRMDataMapper::KEY_INDEX => "PRI",
                ),
                "UnitShort" => array()
            )
        ),
    );
    $this->DataMapper->setFieldsArrayFor( 
        "unit", 
        $UnitFields["unit"][TRMDataMapper::FIELDS_INDEX],
        TRMDataMapper::READ_ONLY_FIELD
    );
}

/**
 * добавляет к анонимной таблице в DataMapper поля для анализа заказов,
 * 1 - COUNT(`id_product`) - количество товаров одного типа
 * 2 - SUM(`count`) - суммарное количество заказов каждого товара,
 */
private function addAnalyticField()
{
    // количество заказов с товаром
    $FieldState = array(
                TRMDataMapper::STATE_INDEX => TRMDataMapper::READ_ONLY_FIELD, 
                TRMDataMapper::TYPE_INDEX => "int(11)", 
                TRMDataMapper::DEFAULT_INDEX => "0", 
                TRMDataMapper::KEY_INDEX => "", 
                TRMDataMapper::EXTRA_INDEX => "", 
                TRMDataMapper::ALIAS_INDEX => "cnt", 
                TRMDataMapper::QUOTE_INDEX => TRMDataMapper::NOQUOTE, 
                TRMDataMapper::COMMENT_INDEX => "Количество заказов с товаром",
            );
    $this->DataMapper->setField(
            "", 
            "COUNT(`id_product`)", 
            $FieldState, 
            TRMDataMapper::READ_ONLY_FIELD);
    
    // количество заказонного товара
    $FieldState[TRMDataMapper::TYPE_INDEX] = "double";
    $FieldState[TRMDataMapper::ALIAS_INDEX] = "qty";
    $FieldState[TRMDataMapper::COMMENT_INDEX] = "Количество товара в заказах";
    $this->DataMapper->setField(
            "", 
            "SUM(`count`)", 
            $FieldState, 
            TRMDataMapper::READ_ONLY_FIELD);

    $this->DataSource->setGroupField("new_order_products.id_product");
}

/**
 * @param double $MinCount - минимальное количество заказов товара одного типа
 * @param double $MinQuantity - минимальное заказонное количество товаров одного типа
 * 
 * @return TRMDataObjectsCollection - коллекция с объектами, заполненными данными
 */
public function getProductSortedByOrdersCount($MinCount = 0, $MinQuantity = 0)
{
    $this->addAnalyticField();
    $this->DataSource->addHavingParam( "", "cnt", $MinCount, ">=" );
    $this->DataSource->addHavingParam( "", "qty", $MinQuantity, ">=" );
    
    $ProductsList = $this->getAll();
    
    if($ProductsList)
    {
        foreach($ProductsList as $Product)
        {
            $Product->setData("Analytic", "cnt", $Product->getData("", "COUNT(`id_product`)") );
            $Product->setData("Analytic", "qty", $Product->getData("", "SUM(`count`)") );
        }
    }

    return $ProductsList;
    
//    $query = "SELECT *, COUNT(`id_product`) `cnt`, SUM(`count`) `qty` 
//        FROM `new_order_products` 
//        GROUP BY `id_product` HAVING COUNT(`id_product`) > 0 
//        ORDER BY `cnt`";
}
/**
 * @param double $MinQuantity - минимальное заказонное количество товаров одного типа
 * 
 * @return TRMDataObjectsCollection - коллекция с объектами, заполненными данными
 */
public function getProductSortedByOrdersQuantity($MinQuantity = 0)
{
    $this->addAnalyticField();
    $this->DataSource->addHavingParam( "", "qty", $MinQuantity, ">=" );
    $this->DataSource->setOrderField("qty", true, TRMSqlDataSource::NEED_QUOTE);

    $ProductsList = $this->getAll();
    
    if($ProductsList)
    {
        foreach($ProductsList as $Product)
        {
            $Product->setData("Analytic", "cnt", $Product->getData("", "COUNT(`id_product`)") );
            $Product->setData("Analytic", "qty", $Product->getData("", "SUM(`count`)") );
        }
    }    
    return $ProductsList;

//    $query = "SELECT *, COUNT(`id_product`) `cnt`, SUM(`count`) `qty` 
//        FROM `new_order_products` 
//        GROUP BY `id_product` HAVING COUNT(`count`) > 0 
//        ORDER BY `qty`";
}

/**
 * @return int - возвращает общее кол-во записей в таблице
 */
public function getTotalCount()
{
    $Query = "SELECT COUNT(*) FROM (
        SELECT COUNT(`id_product`) 
        FROM `new_order_products`
        GROUP BY `id_product`) T";

    $Row = $this->DataSource->
            executeQuery($Query)->
            fetch_row();
    return $Row[0];
}


} // NewOrderProductRepository