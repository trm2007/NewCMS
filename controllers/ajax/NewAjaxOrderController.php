<?php

namespace NewCMS\Controllers\AJAX;

use NewCMS\Domain\NewOrder;
use NewCMS\Domain\NewOrderProduct;
use NewCMS\Libs\Logger\NewGuestVisited;
use TRMEngine\Exceptions\TRMException;

/**
 * обработка AJAX-запросов для заказов
 */
class NewAjaxOrderController extends NewAjaxCommonController
{

/**
 * выводит в виде JSON 
 * список товаров отсортированных по количеству визитов,
 * по цмолчанию возвращаются первые 10 самых посещаемых товаров,
 * если передан JSON-массив, 
 * то 1-м элементом должна идти стартовая позиция для выборки,
 * а 2-м - количество получаемых товаров
 */
public function actionGetTopOrderedProducts()
{
    $json = file_get_contents('php://input');
    if( !$json )
    {
        throw new TRMException("GetTopOrderedProducts - неверные параметры запроса!");
    }

    $OrderProdRep = $this->_RM->getRepository(NewOrderProduct::class); // new NewOrderProductRepository();
    $Config = json_decode($json, true);
    if( isset($Config[1]) && $Config[1] > 0 )
    {
        $OrderProdRep->setLimit( $Config[1],isset($Config[0]) ? $Config[0] : 0 );
    }
    if( isset($Config[2]) )
    {
        $matches = array();
        preg_match("#[^.]+$#", $Config[2], $matches);
        $SortFieldName = $matches[0];
        $OrderProdRep->clearOrder();
        $OrderProdRep->setOrderField($SortFieldName, isset($Config[3]) ? $Config[3] : false );
    }
    echo json_encode(array($OrderProdRep->getTotalCount(), $OrderProdRep->getProductSortedByOrdersCount() ));

//    echo json_encode($ProductList);
}

/**
 * формирует JSON всех покупателей
 */
public function actionGetCustomersList()
{
    $json = file_get_contents('php://input');
    if( !$json )
    {
        throw new TRMException("GetCustomersList - неверные параметры запроса!");
    }

    $OrderRep =  $this->_RM->getRepository(NewOrder::class); // new NewOrderRepository();
    $Config = json_decode($json, true);
    if( isset($Config[1]) && $Config[1] > 0 )
    {
        $OrderRep->setLimit( $Config[1],isset($Config[0]) ? $Config[0] : 0 );
    }
    if( isset($Config[2]) )
    {
        // вычисляем по какому полю сортировка
        $matches = array();
        preg_match("#[^.]+$#", $Config[2], $matches);
        $SortFieldName = $matches[0];
        $OrderRep->clearOrder();
        $OrderRep->setOrderField($SortFieldName, isset($Config[3]) ? $Config[3] : false );
    }
    
    echo json_encode( array( $OrderRep->getTotalEmails(), $OrderRep->getCustomersList($Config[2]) ) );
}

/**
 * все просмотренные страницы в рамках одной сессии
 */
public function actionGetVisitsForSession()
{
    $id = file_get_contents('php://input');
    if( !$id )
    {
        $id = "52ndduev9v4eb2m5o58e9a60eu";
//        echo "";
//        return;
    }
    
    echo json_encode( $this->_RM->getRepository(NewGuestVisited::class)->getVisitsForSession($id) );
}


/**
 * все просмотренные заказчиком страницы, если указал Email,
 * и заказы
 */
public function actionGetVisitsForEmail()
{
    $Email = file_get_contents('php://input');
    if( !$Email )
    {
        $Email = "opk2000@yandex.ru";
    }

    $Rep = $this->_RM->getRepository(NewOrder::class); // new NewOrderRepository();
    
    $Sessions = $Rep->getAllSessionFor($Email);
    
    if( !$Sessions || !$Sessions->count() )
    {
        echo json_encode(null);
        return;
    }
    $IdStr = "";
    foreach( $Sessions as $Session )
    {
        $IdStr .= "\"" . $Session["new_orders"]["session_id"] . "\",";
    }
    $IdStr = rtrim($IdStr,",");
    
    $Rep2 = $this->_RM->getRepository(NewGuestVisited::class);

    $Rep2->addCondition("new_guest_visited", "session_id", $IdStr, "IN");
    
    $ResArr = array();
    $ResArr["visited"] = $Rep2->getAll();
    
    $Orders = $Rep->getAllOrderFor($Email);

    if( !$Orders )
    {
        $ResArr["orders"] = null;
    }
    else
    {
        $ResArr["orders"] = $Orders;
    }
    $IdStr = "";
    foreach( $Orders as $Order )
    {
        $IdStr .= "\"" . $Order["new_orders"]["id"] . "\",";
    }
    $IdStr = rtrim($IdStr,",");
    
    $Rep3 = $this->_RM->getRepository(NewOrderProduct::class); // new NewOrderProductRepository();
    
    $Rep3->addCondition("new_order_products", "id_order", $IdStr, "IN");
    
    $ResArr["products"] = $Rep3->getAll();
    
    echo json_encode( $ResArr );
}


} // NewAjaxOrderController
