<?php

namespace NewCMS\Repositories;

use NewCMS\Domain\NewOrder;
use TRMEngine\DataMapper\TRMDataMapper;
use TRMEngine\DataObject\Interfaces\TRMDataObjectsCollectionInterface;
use TRMEngine\DataObject\TRMDataObjectsCollection;
use TRMEngine\DataSource\Interfaces\TRMDataSourceInterface;

class NewOrderRepository extends NewRepository
{
static protected $DataObjectMap = array(
    "new_orders" => array(
        TRMDataMapper::STATE_INDEX => TRMDataMapper::FULL_ACCESS_FIELD,
        TRMDataMapper::FIELDS_INDEX => array(
            "id" => array(
                TRMDataMapper::KEY_INDEX => "PRI",
            ),
        ),
    ),
);

public function __construct(TRMDataSourceInterface $DataSource)
{
    parent::__construct(NewOrder::class, $DataSource);
}


/**
 * добавляет к анонимной таблице в DataMapper поля для анализа заказов,
 * 1 - COUNT(`email`) - количество email одного покупателя = кол-во заказов с одного Email
 */
private function addAnalyticField()
{
    // количество заказов у пользователя с email
    $FieldState = array(
                TRMDataMapper::STATE_INDEX => TRMDataMapper::READ_ONLY_FIELD, 
                TRMDataMapper::TYPE_INDEX => "varchar(255)", 
                TRMDataMapper::DEFAULT_INDEX => "0", 
                TRMDataMapper::KEY_INDEX => "", 
                TRMDataMapper::EXTRA_INDEX => "", 
                TRMDataMapper::ALIAS_INDEX => "cnt", 
                TRMDataMapper::QUOTE_INDEX => TRMDataMapper::NOQUOTE, 
                TRMDataMapper::COMMENT_INDEX => "Количество заказов пользователя с заданным email"
            );
    $this->DataMapper->setField(
            "", 
            "COUNT(`email`)", 
            $FieldState, 
            TRMDataMapper::READ_ONLY_FIELD);

    $this->DataSource->setGroupField("new_orders.email");
}


/**
 * @param double $MinCount - минимальное количество заказов товара одного типа
 * 
 * @return TRMDataObjectsCollection - коллекция с объектами, заполненными данными
 */
public function getCustomersList( $MinCount = 0 )
{
    $this->addAnalyticField();
    $this->DataSource->addHavingParam( "", "cnt", $MinCount, ">=" );
    
    $CustomersList = $this->getAll();
    
    if($CustomersList)
    {
        foreach( $CustomersList as $Customer )
        {
            $Customer->setData("Analytic", "cnt", $Customer->getData("", "COUNT(`email`)") );
        }
    }
    
    return $CustomersList;
}
/**
 * возвращает все сесии, в которых работал пользователь,
 * который совершал покупки указывая определенный Email
 * 
 * @param string $Email
 * 
 * @return TRMDataObjectsCollectionInterface - коллекция сессий
 */
public function getAllSessionFor($Email)
{
    $this->DataSource->setGroupField("new_orders.session_id");
//    $this->DataSource->addHavingParam("new_orders", "email", $Email);
    return $this->getBy("new_orders", "email", $Email);
}
/**
 * возвращает все ID-заказов пользователя,
 * который совершал покупки указывая определенный Email
 * 
 * @param string $Email
 * 
 * @return TRMDataObjectsCollectionInterface - коллекция сессий
 */
public function getAllOrderFor($Email)
{
    $this->DataSource->setGroupField("new_orders.id");
//    $this->DataSource->addHavingParam("new_orders", "email", $Email);
    return $this->getBy("new_orders", "email", $Email);
}

/**
 * @return int - возвращает общее кол-во разных E-mail в таблице
 */
public function getTotalEmails()
{
    $Query = "SELECT COUNT(*) FROM (
        SELECT COUNT(`email`) 
        FROM `new_orders`
        GROUP BY `email`) T";

    $Row = $this->DataSource->
            executeQuery($Query)->
            fetch_row();
    return $Row[0];
}


} // NewOrderRepository

