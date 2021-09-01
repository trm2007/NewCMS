<?php

namespace NewCMS\Controllers;

use NewCMS\Controllers\BaseController;
use NewCMS\Domain\Exceptions\NewProductsWrongIdExceptions;
use NewCMS\Domain\NewComplexProduct;
use NewCMS\Domain\NewGroup;
use NewCMS\Domain\NewLiteProduct;
use NewCMS\Domain\NewLiteProductForCollection;
use NewCMS\Domain\NewProductFeature;
use NewCMS\Libs\NewHelper;
use NewCMS\Libs\TRMValuta;
use NewCMS\Repositories\Exceptions\NewGroupWrongNumberException;
use NewCMS\Widgets\GroupCrumbs;
use NewCMS\Widgets\NewFeaturesSelector;
use NewCMS\Widgets\NewLastProducts;
use NewCMS\Widgets\NewPagination;
use NewCMS\Widgets\NewPrestigeProducts;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use TRMEngine\DataSource\TRMSqlDataSource;
use TRMEngine\Exceptions\TRMException;
use TRMEngine\Repository\Exceptions\TRMRepositoryNoDataObjectException;

/**
 *  контроллер для отображения групп товаров и самих товаров
 */
class MainController extends BaseController
{

public function actionIndex()
{
    $GroupRep = $this->_RM->getRepository(NewGroup::class); // new NewGroupRepository();
    $GroupRep->setCurrentGroupId(\GlobalConfig::$ConfigArray["StartGroup"]);
    $GroupRep->setOrderBy();
    $GroupRep->setPresentFlagCondition();
    $GroupList = $GroupRep->getAll();
   
    $Title = ucfirst(\GlobalConfig::$ConfigArray["CommonTitle"]);
    $this->view->setTitle( $Title . " - " . \GlobalConfig::$ConfigArray["SiteName"] );
    $this->view->setMeta( "keywords", \GlobalConfig::$ConfigArray["CommonKeyWords"] );
    $this->view->setMeta( "description", \GlobalConfig::$ConfigArray["CommonDescription"] );

    $this->view->setVar("GroupList", $GroupList);
    $this->view->setVar("PageTitle",  $Title );

    return $this->view->render();
}

public function actionAbout()
{
    $Title = "О компании " . \GlobalConfig::$ConfigArray["CompanyTitle"];

    $this->view->setTitle( $Title );
    $this->view->setMeta( "keywords", $Title );
    $this->view->setMeta( "description", $Title );
    $this->view->setMeta( "robots", "INDEX,FOLLOW" );

    $this->view->setVar("PageTitle", $Title);

    return $this->view->render();
}

public function actionContacts()
{
    $Title = \GlobalConfig::$ConfigArray["CompanyTitle"] . " - контакты";

    $this->view->setTitle( $Title );
    $this->view->setMeta( "keywords", \GlobalConfig::$ConfigArray["CompanyTitle"] . ", контакты, реквизиты");
    $this->view->setMeta( "description", \GlobalConfig::$ConfigArray["CompanyTitle"] . " - контакты, схема проезда, реквизиты");
    $this->view->setMeta( "robots", "INDEX,FOLLOW" );

    $this->view->setVar( "PageTitle", $Title);

    return $this->view->render();
}


/**
 * группы товаров и список товаров , выбранных по фильтру
 * 
 * @return string - содержимое страницы
 * @throws TRMException
 */
public function actionPrice()
{
    $GroupRepository = $this->_RM->getRepository(NewGroup::class); // new NewGroupRepository();

    $GroupRepository->setFullParentInfoFlag(true);

    $param = $this->Request->attributes->get("param");

    if( empty($param) )
    {
        $Group = $GroupRepository->getById( \GlobalConfig::$ConfigArray["StartGroup"] );
//        throw new TRMException(__METHOD__ . " Не выбран раздел каталога!", 404);
    }
    else
    {
        if( strpos($param, "/")!==false )
        {
            $arr = explode("/", $param);
            $GroupURL = $arr[0];
        }
        else { $GroupURL = $param; }

        $TranslitFieldName = NewGroup::getTranslitFieldName(); //->getTranslitFieldName();

        $Group = $GroupRepository->getOneBy( $TranslitFieldName[0], $TranslitFieldName[1], $GroupURL );
    }

    if( !$Group )
    {
        throw new TRMException(__METHOD__ . " Данные группы получить не удалось [{$GroupURL}]", 404);
    }

    $GroupId = intval($Group->getId());    
    $Canonical = "/".trim(\GlobalConfig::$ConfigArray["pricePrefix"],"/\\");

    if( $GroupId !== \GlobalConfig::$ConfigArray["StartGroup"] )
    {
        $Canonical .= "/".$Group->getTranslit();
    }
    
    $OriginalTitle = $Group->generateGroupFullTitle();

    $Title = $OriginalTitle." - цены";
    $KeyWords = $OriginalTitle." цены";
    if( $Group->getId() == \GlobalConfig::$ConfigArray["GlobalStartGroup"] )
    {
        $Description = \GlobalConfig::$ConfigArray["CommonDescription"] . ". " . $Group["group"]["GroupTitle"];
    }
    else if( !$Group["group"]["GroupPresent"] )
    {
        $Description = "";
    }
    else
    {
        $Description = "Предлагаем купить ".$OriginalTitle.". Цены с НДС. Обширный ассортимент. Есть доставка по Москве и области, отправляем в регионы РФ";
    }
    
    if( strlen(trim($Group->getData("group", "GroupImage") ) ) >0 )
    {
            $this->view->setVar("ImgSrc", $Group->getData("group", "GroupImage"));
    }
    if( strlen(trim($Group["group"]["GroupBigImage"]))>0)
    {
            $this->view->setVar("BigImage", $Group["group"]["GroupBigImage"]);
    }

//--------------------------- FEATURES -------------------------------------------------------------
    $FeaturesSelector = new NewFeaturesSelector($this->getDBObject());
    $FeaturesSelector->setCurrentGroupId($GroupId, $Group["group"]["GroupTranslit"]);
    $FeaturesSelector->selectFeaturesFromURL($param);

//--------------------------- PRODUCTS -------------------------------------------------------------

    $ProductsListRepository = $this->_RM->getRepository(NewLiteProductForCollection::class); // new NewLiteProductForCollectionRepository();
    $ProductsListRepository->setCurrentGroupId($Group->getId());
    $ProductsListRepository->setPresentFlagCondition();


    $MyGoodsList = new NewLiteProductForCollection();

    // если массив выбранных характеристик не пуст, 
    // значит будет сформирован список товаров согласно выбранным характеристикам
    if( !empty($FeaturesSelector->SelectedFeaturesList) )
    {
        $tmpTitle = $FeaturesSelector->generateTitleStrFromURL($param);
        if(strlen($tmpTitle)) { $Title = $OriginalTitle." (".$tmpTitle.") - цены"; }
        $ProductsListRepository->setFeaturesList($FeaturesSelector->SelectedFeaturesList);
    }

    $NumOfGoods = \GlobalConfig::$ConfigArray["PaginationCount"];

    // с какой позиции и сколько выбираем из БД
    $ProductsListRepository->setLimit($NumOfGoods, ($this->page - 1)*$NumOfGoods);

//--------------------------- WHERE PARAMS -------------------------------------------------------------
    // в этой версии параметры сортировки не показываем...
    $this->view->setVar("OrderLink", false);
    
    $SelectedVendorId = $this->Request->query->getInt("VendorId", -1);
    if( -1 !== $SelectedVendorId )
    {
        $ProductsListRepository->
            addCondition("table1", "vendor", $SelectedVendorId);
        $this->view->setVar("VendorId", $SelectedVendorId);
    }
    if( $this->Request->query->get("NotEmptyFlag", null) !== null )
    {
        $this->view->setVar("NotEmptyFlag", true);
        $ProductsListRepository->
            addCondition("table1", "presentcount", "", "<>", "AND")->
            addCondition("table1", "presentcount", "NULL", "<>", "AND")->
            addCondition("table1", "presentcount", "0", "<>", "AND");
    }
    $this->view->setVar("PriceSort", $this->Request->query->get("PriceSort", 1));
//    $ProductsListRepository->setOrderBy("price0", $this->Request->query->getBoolean("PriceSort", true) );

    $MaxPrice = floatval($this->Request->query->get("MaxPrice", 0));
    if( $MaxPrice !== 0.0 )
    {
        $this->view->setVar("MaxPrice", $MaxPrice);
// addCondition($objectname, $fieldname, $data, $operator = "=", $andor = "AND", $quote = TRMSqlDataSource::NEED_QUOTE, $alias = null, $dataquote = TRMSqlDataSource::NEED_QUOTE )
        $ProductsListRepository->addCondition(
            "table1", 
            "price0", 
            "CASE WHEN `valuta`=1 THEN " . $MaxPrice . "*100/(100+`pr3`)"
                . " ELSE CASE WHEN `valuta`=2 THEN " . TRMValuta::convert($MaxPrice, 1, 2) . "*100/(100+`pr3`)"
                . " ELSE CASE WHEN `valuta`=3 THEN " . TRMValuta::convert($MaxPrice, 1, 3) . "*100/(100+`pr3`)"
                . " END END END", 
            "<", 
            "AND",
            TRMSqlDataSource::NEED_QUOTE,
            null,
            TRMSqlDataSource::NOQUOTE 
        );
    }

    // список товаров из группы и подгрупп,
    // если заданы, то с выбранными характеристиками,
    // за выбор отвечает $FeaturesSelector...
    // getProductsCount вернет количество записей, которые содержатся в БД по данному запросу
    $CountOfGoods = $ProductsListRepository->getProductsCount();
    
    if( !$CountOfGoods )
    {
        $Title = $OriginalTitle." ( с выбранными характеристиками найти товары не удалось )";
    }
    else
    {
        $MyGoodsList = $ProductsListRepository->getAll();
        $this->view->setVar("MyGoodsList", $MyGoodsList);
    }

//--------------------------- PRESTIGE -------------------------------------------------------------
    // создаем вид для популярных товаров, если они есть и если не выбраны характеристики
    if( strpos($param, "-eqv-") === false )
    {
        $PrestigeProductObject = $this->DIC->get(NewPrestigeProducts::class);
        $PrestigeProductObject->setGroupId($GroupId);
        if( $SelectedVendorId > -1 )
        {
            $PrestigeProductObject->setVendorId($SelectedVendorId);
        }
        $PrestigeProducts = $PrestigeProductObject->getPrestigeProducts();

        if( $PrestigeProducts && $PrestigeProducts->count()  )
        {
            $this->view->setVar("PrestigeProducts", $PrestigeProducts);
        }
    }

//--------------------------- PAGINATION -------------------------------------------------------------
    // формируем блок ссылок постраничной навигации
    $MyPaginationClass = new NewPagination($CountOfGoods, $NumOfGoods);
    $MyPaginationClass->SetCurrentPageFromURI();
    $MyPaginationClass->GenerateLinksList();
    $this->view->setVar("PaginationLinks", $MyPaginationClass);

//--------------------------- CRUMBS -------------------------------------------------------------
    $MyCrumbs = new GroupCrumbs();

    GroupCrumbs::getParents($this->getDBObject(), $GroupId, $MyCrumbs);
    $this->view->setVar("MyCrumbs", $MyCrumbs);

//--------------------------- SUBGROUP LIST -------------------------------------------------------------
    $PageTitle = $Title;

    if($this->page <= 1)
    {
        $GroupRepository->setCurrentGroupId( $Group->getId() );
        $GroupRepository->setPresentFlagCondition();
        $GroupRepository->setOrderField("GroupOrder");
        $GroupList = $GroupRepository->getAll();

        if( $GroupList && $GroupList->count() )
        {
            $this->view->setVar("GroupList", $GroupList);
        }

        $this->view->setVar("GroupComment", $Group["group"]["GroupComment"]);
    }
    else
    {
        $PageTitle .= ", страница {$this->page}";
    }

//--------------------------- SET VARS -------------------------------------------------------------
    $this->view->setVar("GroupPresent", $Group["group"]["GroupPresent"]);

//    $FeaturesSelector->generateFeaturesValsArray();
    $this->view->setVar("FeaturesSelector", $FeaturesSelector);

    $this->view->setTitle( htmlspecialchars($Title) );
    $this->view->setMeta("description", htmlspecialchars($Description) );
    $this->view->setMeta("keywords", 
            str_replace(array("\"","\'", "<", ">", "(", ")", "{","}", "[", "]", ".", ","), " ", $KeyWords) );
    $this->view->setCanonical( $Canonical );

    $this->view->setVar("PageTitle", $PageTitle);
//    $this->view->setVar("Description", $Description);
    $this->view->setVar("OriginalTitle", $OriginalTitle);
    $this->view->setVar("CountOfGoods", $CountOfGoods);
    $this->view->setVar("StartGroup", $GroupId );
    $this->view->setVar("ShowDescriptionFlag", true);

    $this->view->setVar("catalogflag", true);
    $this->view->addCSS(TOPIC . "/css/forcatalogpage.css", true);
    $this->view->addCSS(TOPIC . "/css/selector.css", false);

//--------------------------- GROUP++ -------------------------------------------------------------
    $Group->setData( "group", "GroupVisits", $Group["group"]["GroupVisits"]+1 );
    $GroupRepository->update($Group);
    $GroupRepository->doUpdate();
    
    if( !$Group["group"]["GroupPresent"] )
    {
        $Code = 404;
    }
    else { $Code = 200; }
    
    ob_start();
    $this->view->render();

    return new Response( ob_get_clean(), $Code ); //(string)$this->view, $Code);
}

/**
 * action что бы работать с отображением страницы товаров
 * 
 * @return string - содержимое страницы
 * @throws Exception - если товар не найден, выбрасывается исключение
 */
public function actionCatalog()
{
    $param = $this->Request->attributes->get("param");
    //если не передан адрес товара, то такой страницы нет - 404 ошибка
    if( empty($param) )
    {
        throw new \Exception( __METHOD__ . " Не передан адрес товара!", 404);
    }

    $ComplexRepository = $this->_RM->getRepository(NewComplexProduct::class); // new NewComplexProductRepository();

    try
    {
        $MyComplect1 = $ComplexRepository->getOneBy( "table1", "PriceTranslit", $param );
    }
    catch(TRMRepositoryNoDataObjectException $e )
    {
        throw new NewProductsWrongIdExceptions("Документ с адресом {$param} не найден!", 404, $e);
    }

    $ParentFeaturesList = null;
    $ParentProduct = null;
    
    $LiteProduct = $MyComplect1->getLiteProduct();
    
    if( $LiteProduct["table1"]["ParentId"] )
    {
        $ParenProductId = $LiteProduct["table1"]["ParentId"];
        $ParentFeaturesList = $this->_RM->getRepository(NewProductFeature::class)
//                (new NewProductFeatureRepository())
                ->getBy("goodsfeatures", "ID_Price", $ParenProductId);
        
        $ParentProduct = $this->_RM->getRepository(NewLiteProduct::class)
                //(new NewLiteProductRepository)
                ->getById($ParenProductId);
        
        $ProductFeaturesList = $MyComplect1["ProductFeaturesCollection"];
        // удаляем из родительской коллекции характеристики, 
        // которые есть в самом товаре-модели
        foreach( $ProductFeaturesList as $ProductFeature )
        {
            foreach( $ParentFeaturesList as $Index => $ParentProductFeature )
            {
                if( $ParentProductFeature->getData("goodsfeatures", "ID_Feature") 
                        == $ProductFeature->getData("goodsfeatures", "ID_Feature") 
                )
                {
                    $ParentFeaturesList->removeDataObject($Index);
                }
                
            }
        }
        
        $this->view->setVar("ParentProduct", $ParentProduct);
        $this->view->setVar("ParentFeaturesList", $ParentFeaturesList);
    }

//--------------------------- CRUMBS -------------------------------------------------------------
    $MyCrumbs = new GroupCrumbs();

    GroupCrumbs::getParents($this->getDBObject(), $LiteProduct["table1"]["Group"], $MyCrumbs);
    $this->view->setVar("MyCrumbs", $MyCrumbs);

//--------------------------- META -------------------------------------------------------------

    $Title = $LiteProduct["table1"]["Name"].", "
            . $MyComplect1->getMainDataObject()->getVendorObject()["vendors"]["VendorName"]
            . ", цена";
    $Canonical = "/"
            . trim(\GlobalConfig::$ConfigArray["catalogPrefix"], "/\\")
            . "/" . $LiteProduct["table1"]["PriceTranslit"]; // $_SERVER["REQUEST_URI"];
    $KeyWords = $Title;

    //описание для страницы мета тег!!!
    if(\GlobalConfig::$ConfigArray["PriceColumnCount"] == 1)
    { $Price = $LiteProduct->getData("table1", "Price3"); }
    if(\GlobalConfig::$ConfigArray["PriceColumnCount"] == 2)
    { $Price = $LiteProduct->getData("table1", "Price2"); }
    if(\GlobalConfig::$ConfigArray["PriceColumnCount"] == 3)
    { $Price = $LiteProduct->getData("table1", "Price1"); }

    $Description = ( $LiteProduct->getData("table1", "price0")
            ? ("Цена от ".($Price)." руб/".$LiteProduct->getData("unit", "UnitShort"))
            : "Привлекательные цены").". "
                . $LiteProduct["table1"]["Name"].", производство "
                . $MyComplect1->getMainDataObject()->getVendorObject()["vendors"]["VendorName"]
                . ". Доставка по Москве и МО, отправляем в регионы РФ. Скидки";


// ************************ LINK ************************************************
    $IdsArr = NewHelper::createLinkRows(
        $this->getDBObject(),
        10, 
        $LiteProduct->getData("table1", "ID_price"), 
        $LiteProduct->getData("table1", "Group") );
    if( !empty($IdsArr) )
    {
        $IdStr = implode( ",", $IdsArr );
        $LinkRep = $this->_RM->getRepository(NewLiteProductForCollection::class); // new NewLiteProductForCollectionRepository();

        $LinkRep->setPresentFlagCondition();
        $LinkRep->addCondition("table1", "ID_price", $IdStr, "IN");

        $LinkProductsList = $LinkRep->getAll();
        if( $LinkProductsList )
        {
            $this->view->setVar("LinkProductsList", $LinkProductsList);
        }
    }

//--------------------------- setVar -------------------------------------------------------------

    $this->view->addCSS(TOPIC . "/css/forcatalogpage.css", true);
    $this->view->addCSS(TOPIC . "/css/selector.css", false);
    $this->view->setTitle( htmlspecialchars($LiteProduct["table1"]["Name"]) );
    $this->view->setMeta("description", htmlspecialchars($Description) );
    $this->view->setMeta("keywords", str_replace(array("\"","\'", "<", ">", "(", ")", "{","}", "[", "]", ".", ","), " ", $KeyWords) );
    $this->view->setCanonical( $Canonical );
    $this->view->setVar("Description", $Description);

    $this->view->setVar("noShowImage", true);

//    $this->view->setVar("catalogflag", true);
    $this->view->setVar("StartGroup", $LiteProduct["table1"]["Group"] );

    
    $this->view->setVar("ProductData", $LiteProduct["table1"] );
    
    $this->view->setVar("VendorData", $MyComplect1->getMainDataObject()->getVendorObject()["vendors"] );
    $this->view->setVar("UnitData", $LiteProduct["unit"] );
    $this->view->setVar("GoodsDescriptionData", $LiteProduct["goodsdescription"] );
    $this->view->setVar("ComplectData", $MyComplect1->getChildCollection("ComplectCollection") );
    $this->view->setVar("GoodsFeatures", $MyComplect1->getChildCollection("ProductFeaturesCollection") );
    $this->view->setVar("Images", $MyComplect1->getChildCollection("ImagesCollection") );

    //$this->view->setVar("LinkContent", true);

//     запишем в куки, что был просмотрен этот товар
//     для iOS это не срабортает, поэтому Cookie формируются на клиенте!!!
    //(new NewLastProducts( $this->_RM->getRepository(NewLiteProductForCollection::class) ))
    $this->DIC->get(NewLastProducts::class)
            ->setLastProducts( $MyComplect1->getId() );

    // увеличиваем кол-во просмотров товара на 1
    $LiteProduct->setData( "table1", "Visits", $MyComplect1->getData( "table1", "Visits")+1 );
    
    $LiteProductRep = $this->_RM->getRepositoryFor($LiteProduct);
    
    $LiteProductRep->update($LiteProduct);
    $LiteProductRep->doUpdate();

    if( !$LiteProduct["table1"]["present"] )
    {
        $Code = 404;
    }
    else { $Code = 200; }
    
    ob_start();
    $this->view->render();

    return new Response( ob_get_clean(), $Code ); //(string)$this->view, $Code);
}

/**
 * редиректы со старых адресов товаров,
 * например, descr.php?ID_Price=13354
 * 
 * @return RedirectResponse
 * @throws NewProductsWrongIdExceptions
 */
public function actionDescrReDirect()
{
    $param = $this->Request->query->getInt("ID_Price");
    
    $ProductRep = $this->_RM->getRepository(NewLiteProduct::class); // new NewLiteProductRepository();
    $MyComplect1 = $ProductRep->getById($param);

    if( !$MyComplect1 )
    {
        throw new NewProductsWrongIdExceptions("Не удалось получить данные товара [".$param."] !", 404);
    }
    return new RedirectResponse(\GlobalConfig::$ConfigArray["catalogPrefix"]."/".$MyComplect1["table1"]["PriceTranslit"], 301);
}

/**
 * редиректы со старых адресов групп,
 * например, catalog.php?group=429
 * 
 * @return RedirectResponse
 * @throws NewGroupWrongNumberException
 */
public function actionCatalogReDirect()
{
    $param = $this->Request->query->getInt("group");
    
    $GroupRep = $this->_RM->getRepository(NewGroup::class); // new NewGroupRepository();
    
    $Group = $GroupRep->getById($param);

    if( !$Group )
    {
        throw new NewGroupWrongNumberException("Не удалось получить данные для группы [".$param."] !", 404);
    }
    return new RedirectResponse(\GlobalConfig::$ConfigArray["pricePrefix"]."/".$Group["group"]["GroupTranslit"], 301);
}


} // MainController