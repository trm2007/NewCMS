<?php

namespace NewCMS\Controllers\AJAX;

use GlobalConfig;
use NewCMS\Controllers\AJAX\NewAjaxCommonController;
use NewCMS\Domain\NewComplexProduct;
use NewCMS\Domain\NewGroup;
use NewCMS\Libs\TRMValuta;
use NewCMS\Yandex\NewYandexMarketProduct;
use NewCMS\Yandex\NewYandexMarketProductRepository;
use Symfony\Component\HttpFoundation\Request;
use TRMEngine\DiContainer\TRMDIContainer;

/**
 * SimpleXMLExtend - расширенный класс SimpleXMLElement 
 * для корректного добавления добалвения <[CDATA[...]]> через DOM
 */
class SimpleXMLExtend extends \SimpleXMLElement
{
public function addCData($nodename,$cdata_text)
{
    $node = $this->addChild($nodename);
    $node = dom_import_simplexml($node);
    $no = $node->ownerDocument;
    $node->appendChild($no->createCDATASection($cdata_text));
}

} // SimpleXMLExtend

/**
 * обработка AJAX-запросов для Yandex (Market и других сервисов)
 */
class NewAjaxYandexController extends NewAjaxCommonController
{
/**
 * @var NewYandexMarketProductRepository 
 */
protected $Rep;

public function __construct(Request $Request, TRMDIContainer $DIC)
{
    parent::__construct($Request, $DIC);
    $this->Rep = $this->_RM->getRepository(NewYandexMarketProduct::class);
}
    

/**
 * товар с Id есть в таблице товаров для YandexMarket вернет true в JSON,
 * иначе false
 */
public function actionExistsProduct()
{
    $ProductId = file_get_contents('php://input');
    
    echo json_encode( $this->Rep->existsProduct(json_decode($ProductId)) );
}

/**
 * Добавляет товар с Id в таблицу товаров для YandexMarket
 */
public function actionAddProduct()
{
    $ProductId = file_get_contents('php://input');
    
    echo json_encode( $this->Rep->addProduct(json_decode($ProductId)) );
}

/**
 * Удаляет товар с Id из таблицы товаров для YandexMarket
 */
public function actionRemoveProduct()
{
    $ProductId = file_get_contents('php://input');
    
    echo json_encode( $this->Rep->removeProduct(json_decode($ProductId)) );
}

/**
 * возвращает JSON-массив с ID товаров, 
 * которые присутсвуют в Яндекс-Маркете для группы
 */
public function actionGetMarketIds()
{
    $GroupId = file_get_contents('php://input');
    
    echo json_encode( $this->Rep->getIdArray(json_decode($GroupId)) );
}

/**
 * Формирует полный URL
 * 
 * @param string $LocalURL
 * @param string $Prefix
 * @return string
 */
private function getFullURL($LocalURL, $Prefix = "")
{
    $Res = rtrim(GlobalConfig::$ConfigArray["CommonURL"], "/\\");
    if( !empty($Prefix) )
    {
        $Res .= "/" . trim($Prefix, "/\\"); 
    }
    $Res .= "/" . ltrim($LocalURL, "/\\");
    return $Res;
}

/**
 * формирует yml-файл с товарами для Yandex Market-a
 */
public function actionGenerateYML($FilePath)
{
    $IdsStr = implode( ", ", $this->Rep->getIdArray());
    if( empty($IdsStr) ) { return; }

    //$GroupRep = new \NewCMS\Repositories\NewGroupRepository();
    $GroupRep = $this->_RM->getRepository(NewGroup::class);
    $GroupRep->setFullParentInfoFlag();
    $GroupRep->setSubGroupsFlag();
    $GroupRep->setPresentFlagCondition();
    $GroupRep->setOrderField("GroupID_parent");
    $GroupRep->setCurrentGroupId(GlobalConfig::$ConfigArray["StartGroup"]);
    $GroupList = $GroupRep->getAll();

//\TRMEngine\Helpers\TRMLib::sp(GlobalConfig::$ConfigArray["StartGroup"]);
//header("Content-Type: text/html; charset=utf-8", true);
//foreach( $GroupList as $Group )
//{
//    echo htmlspecialchars(trim($Group["group"]["GroupTitle"])) 
//        . ", id - " . $Group->getId() 
//        . ", parentId - " . $Group["group"]["GroupID_parent"]
//        . "<br>" . PHP_EOL;
//}
//exit;

    $Rep = $this->_RM->getRepository(NewComplexProduct::class); // new \NewCMS\Repositories\NewComplexProductRepository();
    
    $Rep->addCondition("table1", "ID_price", $IdsStr, "IN");
    $ProdCollection = $Rep->getAll();
    
    $XMLStartStr = "<?xml version=\"1.0\" encoding=\"UTF-8\"?><yml_catalog date=\""
            . date("Y-m-d H:i", time() ) // "2017-02-05 17:22"
            . "\" />";
    $xml = new SimpleXMLExtend($XMLStartStr); //\SimpleXMLElement($XMLStartStr);
    $Shop = $xml->addChild("shop");
    $Shop->addChild( "name", GlobalConfig::$ConfigArray["SiteName"] );
    $Shop->addChild( "company", "ООО Офис под ключ" );
    $Shop->addChild( "url", GlobalConfig::$ConfigArray["CommonURL"] );

    $Shop->addChild( "platform", "NewCMS@TRMEngine" );
    $Shop->addChild( "version", "0.1" );
    $Shop->addChild( "agency", "TRMEngine" );
    $Shop->addChild( "email", "trm@mail.ru" );


    $Currencies = $Shop->addChild( "currencies" );
    foreach( TRMValuta::$Valuta as $Index => $Rate )
    {
        if( intval($Index) !== 0 ) { continue; }
        $Currenciy = $Currencies->addChild( "currency" );
        $Currenciy->addAttribute("id", $Index);
        $Currenciy->addAttribute("rate", $Rate);
//        $Currenciy->addAttribute("rate", "CBRF");
        if( intval($Rate) !== 1 )
        {
            $Currenciy->addAttribute("plus", "1.5");
        }
    }

    $Categories = $Shop->addChild( "categories" );
    foreach( $GroupList as $Group )
    {
        $Category = $Categories->addChild( "category", htmlspecialchars(trim($Group["group"]["GroupTitle"])) );
        $Category->addAttribute("id", $Group->getId() );
        if( $Group["group"]["GroupID_parent"] )
        {
            $Category->addAttribute("parentId", $Group["group"]["GroupID_parent"] );
        }
    }

    $Delivery = $Shop->addChild("delivery-options");
    $DeliveryOption = $Delivery->addChild("option");
    $DeliveryOption->addAttribute("cost", "2500");
    $DeliveryOption->addAttribute("days", "1");
    $Offers = $Shop->addChild("offers");

    foreach( $ProdCollection as $Product )
    {
        $LiteProduct = $Product->getLiteProduct();
        $Offer = $Offers->addChild("offer");
        $Offer->addAttribute("id", $Product->getId() );
//        $Offer->addAttribute("type", "vendor.model" );
        
        // typePrefix - используется только в произвольном типе vendor.model
//        $Offer->addChild("typePrefix", htmlspecialchars($Product->getMainDataObject()->getGroupObject()->generateGroupFullTitle()));
        $Offer->addChild("vendor", $Product->getMainDataObject()->getVendorObject()->getData("vendors", "VendorName") );
        // model - используется только в произвольном типе vendor.model
//        $Offer->addChild("model", htmlspecialchars($LiteProduct->getData("table1", "Name")));
        // model - для упрощенного типа используется name
        $Offer->addChild("name", htmlspecialchars($LiteProduct->getData("table1", "Name")));

        if( !empty($LiteProduct["table1"]["articul"]) )
        {			
            $Offer->addChild("vendorCode", htmlspecialchars($LiteProduct->getData("table1", "articul")));
        }
        $Offer->addChild("url", $this->getFullURL($LiteProduct->getData("table1", "PriceTranslit"), GlobalConfig::$ConfigArray["catalogPrefix"]) );
        $Offer->addChild("price", $LiteProduct->getData("table1", "Price3") );
        $Offer->addChild("currencyId", TRMValuta::$Valutas[1][0] ); // $LiteProduct["table1"]["valuta"]][0] );
        $Offer->addChild("categoryId", $LiteProduct["table1"]["Group"] );
        $Offer->addChild("picture", $this->getFullURL($LiteProduct["table1"]["Image"] .".jpg", GlobalConfig::$ConfigArray["ImageCatalog"]) );

        if( $LiteProduct["goodsdescription"]["GoodsDescription"] )
        {
//            $DescriptionText = "<![CDATA[" . PHP_EOL;
//            $DescriptionText .= $LiteProduct["goodsdescription"]["GoodsDescription"];
//            $DescriptionText .= PHP_EOL . "]]>";
//
//            $Offer->addChild( "description", $DescriptionText );
            $Offer->addCData( "description", $LiteProduct["goodsdescription"]["GoodsDescription"]);
        }
        
        foreach( $Product["ProductFeaturesCollection"] as $Features )
        {
            if( empty($Features["goodsfeatures"]["FeaturesValue"]) ) { continue; }
            $Param = $Offer->addChild( "param", $Features["goodsfeatures"]["FeaturesValue"] );
            $Param->addAttribute( "name", $Features["features"]["FeatureTitle"] );
            if( $Features["features"]["Reserv"] )
            {
                $Param->addAttribute( "unit", $Features["features"]["Reserv"] );
            }
        }

        if( !empty($LiteProduct["table1"]["MinPart"]) && $LiteProduct["table1"]["MinPart"] != 1 )
        {			
            $Offer->addChild("sales_notes", "Предоплата. Минимальная партия {$LiteProduct['table1']['MinPart']} {$LiteProduct['unit']['UnitShort']}" );
        }
        else
        {
            $Offer->addChild("sales_notes", "Необходима предоплата" );
        }
    }

    if($xml->asXML($FilePath)) // "opk_market.yml"))
    {
        header("Content-Type: text/xml; charset=utf-8", true);
        echo $xml->asXML();
    }
    else
    {
        throw new TRMException("Не удалось сформировать YML!");
    }
//    $xml->asXML("/home/podvesno/podvesnoi.ru/docs/opk_market.yml");
}


} // NewAjaxVendorController
