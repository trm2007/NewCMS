<?php

namespace NewCMS\Libs;

use NewCMS\Domain\NewLiteProductForCollection;
use TRMEngine\Cookies\TRMCookie;
use TRMEngine\Repository\TRMRepositoryManager;

/**
 * класс для хранения единицы товара в корзине
 * ссылка на сам товар из БД и его количество
 */
class NewBasketProduct
{
/**
 * @var NewLiteProductForCollection - объект товара
 */
public $Item;
/**
 * @var int - количество товаров ($Item) в корзине
 */
public $Count;

function __construct($id, $count)
{
    $this->Item = new NewLiteProductForCollection();
    $this->Item->setId($id);
    $this->Count = floatval($count);
}

} // BasketGoods


/**
 *  класс корзины заказов, основан на записи и чтения пары ID-товара-кол-во в Cookie
 */
class NewBasket
{
/**
 * @var array - устанавливает от какой цены действует максимальная скидка, средняя колонка и обычная-розничная цена
 */
public $CostLimits = array( 300000, 100000, 1000 );
/**
 * @var string - имя cookie для корзины
 */
private $BasketName;
/**
 *
 * @var array - массив из 3-х сумм цен всех товаров
 */
private $Summ = array();
/**
 * @var string - строка, в которой содержится сам подготовленный Cookie
 */
private $PackageString = "";
/**
 *
 * @var array(NewBasketProduct) - массив с объектами товаров в корзине
 */
public $Goods = array();
/**
 * @var TRMRepositoryManager 
 */
protected $_RM;

/**
 * @param string $RM - имя Cookie с корзиной товаров на машине клиента, 
 * если не задан этот парамет, то используется по умолчанию
 * имя домена без www + _basket, точки . заменяются на подчеркивания _ ,
 * например www.suprtventilator.ru => suprtventilator_ru_basket
 */
function __construct(TRMRepositoryManager $RM, $BasketName = "")
{
    $this->_RM = $RM;
    if( empty($BasketName) )
    {
        $ServerName = filter_input(INPUT_SERVER, "HTTP_HOST", FILTER_SANITIZE_URL);
        $BasketName = str_replace( ".", "_", ltrim($ServerName, "www.") ) . "_basket";
    }
    $this->BasketName = $BasketName;
}
/**
 * упаковывает массив товаров в строку для записи в cookies
 * 
 * @return boolean
 */
function packingGoods()
{
    if( empty($this->Goods) ) return false;

    $this->PackageString = "";
    for($i=0;$i<count($this->Goods); $i++)
    {
            if($i > 0) $this->PackageString .= "|";
//	формат : артикул1=количество1|артикул2=количество2|артикул3=количество3
            $this->PackageString .= $this->Goods[$i]->Item->getId()."-".$this->Goods[$i]->Count;
    }
    return true;
}

/**
 * распаковывает товары из Cookies для корзины в массив Goods, 
 * каждый элемент которого имеет тип NewBasketProduct
 * 
 * @return boolean
 */
private function unpackingGoods()
{
    if( empty($this->PackageString) ) { return false; }
    $this->Goods = array();

    $tmp = explode("|", trim($this->PackageString) ); //создаем массив из строк разделенных в cookies чертой |
    for($i=0;$i<count($tmp); $i++)
    {
        // из каждой полученной стрки извлекаем два значения
        // разделенных минусом по шаблоу: ID-количество 
        $tmpid = explode("-", trim($tmp[$i]) );
        $this->Goods[$i] = new NewBasketProduct( $tmpid[0], floatval($tmpid[1]) ); 
    }
    return true;
}

/**
 * вычисляет общую стоимость (сумму) товаров в корзине
 * @return double - общая стоимость корзины тваров
 */
function calculateSumm()
{
    $this->Summ[0] = 0;
    $this->Summ[1] = 0;
    $this->Summ[2] = 0;
    
    for($i=0;$i<count($this->Goods); $i++)
    {
//        $this->Goods[$i]->Item->calculateProductPrice();
        $Prices = $this->Goods[$i]->Item->getPriceArray();
        $this->Summ[0] += floatval($Prices[0]*$this->Goods[$i]->Count);
        $this->Summ[1] += floatval($Prices[1]*$this->Goods[$i]->Count);
        $this->Summ[2] += floatval($Prices[2]*$this->Goods[$i]->Count);
    }
    
    if( $this->Summ[0] > $this->CostLimits[0] ){ return $this->Summ[0]; }
    else if( $this->Summ[1] > $this->CostLimits[1] ){ return $this->Summ[1]; }
    else if( $this->Summ[2] > $this->CostLimits[2] ){ return $this->Summ[2]; }
    else { return 0; }
}

/**
 * загружает данные товаров, которые в данный момент числятся в корзине, из БД
 * 
 * @return boolean
 */
function initGoodsFromDB()
{
    if( empty($this->Goods) ) { return false; }
    
    $Rep = $this->_RM->getRepository(NewLiteProductForCollection::class);

    for($i=0;$i<count($this->Goods); $i++)
    {
        $this->Goods[$i]->Item = $Rep->getById( $this->Goods[$i]->Item->getId() );
    }

    return true;
}

/**
 * получает список ID-товаров и их количества из Cookie
 * 
 * @return boolean
 */
function getGoodsFromCookies()
{
    $this->PackageString = TRMCookie::get($this->BasketName);
    if(!$this->PackageString) { return false; }

    return $this->unpackingGoods();
}

/**
 * помещает список ID-товаров и их количества в Cookie
 * 
 * @return boolean
 */
function putGoodsToCookies()
{
    if( strlen($this->PackageString)<=0) 
    {
        if(!$this->packingGoods() ) { return false; }
    }

    return TRMCookie::set($this->BasketName, $this->PackageString, 0, "/");
}

/**
 * удаляет массив с товарами и удаляет Cookie
 * 
 * @return boolean
 */
function emptyBasket()
{
    $this->PackageString = "";
    $this->Goods = array();

    return TRMCookie::delete($this->BasketName);
}


} // class NewBasket
