<?php

namespace NewCMS\Widgets;

use NewCMS\Domain\Exceptions\NewProductsWrongIdExceptions;
use NewCMS\Repositories\NewLiteProductForCollectionRepository;
use NewCMS\Views\CMSBaseView;
use TRMEngine\Cookies\TRMCookie;
use TRMEngine\Repository\TRMRepositoryManager;

class NewLastProducts
{
/**
 * @var NewLiteProductForCollectionRepository - репозиторий товаров для коллекции
 */
protected $Rep;
/**
 * @var string - имя cookie-файла на стороне клиента для сохранения последних просмотренных товаров
 */
static $CookiesName = "";
/**
 *
 * @var int - время в секундах, на которое сохраняется cookie-файла с просмотренными товарами,
 * по умолчанию на 90 дней = 60*60*24*90
 */
static $CookieTime = 60*60*24*90;

/**
 * 
 * @param TRMRepositoryManager $RM - репозиторий товаров для коллекции
 */
public function __construct(NewLiteProductForCollectionRepository $Rep)
{
    $this->Rep = $Rep;
}

/**
 * получает список просмотренных товаров из cookie-файла на стороне клиента,
 * если имя файла не задано, то генерируется автоматически на основе имени домена
 * 
 * @return int|null
 */
public function getLastProducts()
{
    if(empty(self::$CookiesName) )
    {
        static::generateCookiesName();
    }
    
    $cookie = TRMCookie::get( static::$CookiesName );
    
    if( empty($cookie) ) { return null; }

    $IdsArr = explode("-", $cookie);
    if( empty($IdsArr) ) { return null; }

    $ids = "";
    foreach( $IdsArr as $Item )
    {
        if( !empty($Item) )
        {
            $ids .= $Item . ",";
        }
    }
    $ids = trim($ids, ",");

    $this->Rep->addCondition( "table1", "ID_price", $ids, "IN");

    return $this->Rep->getAll();
}

/**
 * записывает в cookie номер ID-товара $ID_price,
 * если в cookie уже есть $GoodsCount товаром, то первый удаляется, а новый добавляется в конец
 * если не задано имя cookie-файла, то оно будет сгененрировано автоматически на основе имени домена
 * 
 * @param int $ID_price - ID-товара
 * @param int $GoodsCount - количество хранимых товаров в cookie
 * 
 * @throws NewProductsWrongIdExceptions - если передан пустой ID-товара выбрасывается исключение
 */
public function setLastProducts($ID_price, $GoodsCount = 3)
{
    if(!$ID_price)
    {
        throw new NewProductsWrongIdExceptions("Пустой номер товара!");
    }
    if( empty(self::$CookiesName) )
    {
        static::generateCookiesName();
    }

    $laststr = $ID_price;
    $cookie = TRMCookie::get( self::$CookiesName );
    if( !empty($cookie) )
    {
        $ids = explode("-", $cookie);
        for($i=0,$k=0;$i<count($ids) && $k<($GoodsCount-1); $i++ )
        {
            if( strcmp($ids[$i], $ID_price) <> 0){ $laststr .= ("-".$ids[$i]); $k++;}
        }
    }
    TRMCookie::set( self::$CookiesName, $laststr, time()+self::$CookieTime, "/"); // сохраняем кук на один год
}

/**
 * автоматически генерирует имя cookie-файла из имени домена,
 * заменяя точки на подчеркивания и добавляя _last,
 * например, www.superventilator.ru => www_superventilator_ru_last
 */
public static function generateCookiesName()
{
            //$_SERVER['HTTP_HOST'] . ".last";
    static::$CookiesName = filter_input(INPUT_SERVER, "HTTP_HOST", FILTER_SANITIZE_URL) . ".last";

    static::$CookiesName = str_replace( ".", "_", self::$CookiesName);
}

/**
 * выводит список последних просмотренных товаров из Cookie-файла в вид lastproducts,
 * если список товаров пуст, то ничего не делает
 */
static public function render(NewLastProducts $LastProductsObject)
{
    $LastProducts = $LastProductsObject->getLastProducts();
    // если не получены товары, прекращает работу
    if( !$LastProducts || !$LastProducts->count() )
    {
        return;
    }

    $LastProductsView = new CMSBaseView("lastproducts", null);
    $LastProductsView->setPathToViews( ROOT . TOPIC . "/views/main/inc" );
    $LastProductsView->setVar( "LastProducts", $LastProducts );
    $LastProductsView->render();    
}


} // NewLastProducts