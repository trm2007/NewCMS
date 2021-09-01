<?php

namespace NewCMS\Controllers\AJAX;

use NewCMS\Domain\NewCommonList;
use NewCMS\Domain\NewComplexProduct;
use NewCMS\Domain\NewGroupFeature;
use NewCMS\Domain\NewLiteProduct;
use NewCMS\Domain\NewLiteProductForCollection;
use NewCMS\Domain\NewProductFeature;
use NewCMS\Domain\NewProductImage;
use NewCMS\Domain\NewUnit;
use NewCMS\Domain\NewVendor;
use NewCMS\Libs\TRMValuta;
use TRMEngine\DataObject\TRMTypedCollection;
use TRMEngine\EventObserver\TRMEventManager;
use TRMEngine\Exceptions\TRMException;

/**
 * Обработка AJAX запросов при работе с объектами товаров
 */
class NewAjaxProductController extends NewAjaxCommonController
{

/**
 * получает содержимое составного товара из БД
 */
public function actionGetComplexProduct()
{
    $id = file_get_contents('php://input');

    $ComplexProductRepository = $this->_RM->getRepository(NewComplexProduct::class); 

    $ComplexProduct = $ComplexProductRepository->getById( $id );
    $this->renderComplexProductJSON($ComplexProduct);
}

/**
 * отправляет JSON строку с товаром, 
 * если есть, то с родительским продуктом и его характеристиками
 * 
 * @param NewComplexProduct $ComplexProduct
 */
private function renderComplexProductJSON(NewComplexProduct $ComplexProduct)
{
    $LiteProduct = $ComplexProduct->getLiteProduct();
    $Arr = array();
    $ParenProductId = $LiteProduct["table1"]["ParentId"];
    /*
     * формирует массив из 2-х JSON-строк,
     * 0-я строка - JSON-объект с характеристиками родительского продукта,
     * 1-я строка - JSON-объект самого родительского товара
     */
    if( $ParenProductId )
    {
        $Arr[0] = json_encode($this->getProductFeaturesList($ParenProductId));
        
        $Arr[1] = json_encode(
                $this->_RM->getRepository(NewLiteProduct::class) // 
                //(new NewLiteProductRepository)
                ->getById($ParenProductId)
            );
    }

    echo "[" . json_encode($ComplexProduct);
    if( !empty($Arr) )
    {
        echo ", " . $Arr[0]; //$ParentFeaturesListStr;
        echo ", " . $Arr[1]; //$ParentProductStr;
    }
    echo "]";
}

/**
 * пустой продукт,
 * используется, например, для формирования нового товара во FrontEnd
 */
public function actionGetEmptyComplexProduct()
{
    $ComplexProduct = $this->_RM->getRepository(NewComplexProduct::class)->getNewObject();
//    $ComplexProduct = (new NewComplexProductRepository())->getNewObject();
    
    $this->renderComplexProductJSON($ComplexProduct);
}

public function actionGetEmptyProductImage()
{
    echo json_encode(  $this->_RM->getRepository(NewProductImage::class)->getNewObject() );
//    echo json_encode( (new NewProductImageRepository())->getNewObject() );
}

/**
 * возвращает список товаров для группы
 */
public function actionGetProductsList()
{
    $Rep = $this->_RM->getRepository(NewLiteProductForCollection::class); // new NewLiteProductForCollectionRepository();
    
    $Rep->setOrderBy("item_order", true);
    echo json_encode( 
        $Rep->getBy( "table1", "Group", file_get_contents('php://input') )
    );
}

/**
 * возвращает список характеристик для товара,
 * должен быть передан ID-товара
 */
public function actionGetProductFeaturesList()
{
    $ProductId = file_get_contents('php://input');
    
    $FeaturesList = $this->getProductFeaturesList($ProductId);
    
    echo json_encode($FeaturesList);
}
/**
 * возвращает коллекцию характеристик NewProductFeature для указанного продукта
 * 
 * @param int $ProductId - ID-продукта, для которого нужно получить характеристики
 * 
 * @return TRMTypedCollection
 */
private function getProductFeaturesList($ProductId)
{
    return $this->_RM->getRepository(NewProductFeature::class)->getBy("goodsfeatures", "ID_Price", $ProductId);
}

/**
 * выводит в виде JSON 
 * список товаров отсортированных по количеству визитов,
 * по цмолчанию возвращаются первые 10 самых посещаемых товаров,
 * если передан JSON-массив, 
 * то 1-м элементом должна идти стартовая позиция для выборки,
 * а 2-м - количество получаемых товаров
 */
public function actionGetTopVizitedProducts()
{
    $json = file_get_contents('php://input');

    $ProductRep = $this->_RM->getRepository(NewLiteProductForCollection::class); // new NewLiteProductForCollectionRepository();
    if( $json )
    {
        // array( 0 => StartPosition, 1 => Count, 2 => Descending )
        $Config = json_decode($json, true);
        if( isset($Config[1]) && $Config[1] > 0 )
        {
            $ProductRep->setLimit($Config[1], $Config[0]);
        }
        if( isset($Config[2]) )
        {
            $ProductRep->clearOrder();
            $ProductRep->setOrderField("Visits", $Config[2]);
        }
    }
    $ProductRep->setPresentFlagCondition();
    echo json_encode(array($ProductRep->getTotalCount(), $ProductRep->getAll()));
}

/**
 * сохраняет NewComplexProduct в БД
 */
public function actionUpdateComplexProduct()
{
    $json = file_get_contents('php://input');

    $ComplexProduct = new NewComplexProduct($this->DIC->get(TRMEventManager::class));

    // инициализируем объект из массива, полученного из JSON
    $ComplexProduct->initializeFromArray( json_decode($json, true) );
    
    $Translit = $ComplexProduct->getLiteProduct()->getTranslit();
    if( empty($Translit) ) // || $NewTranslitFlag )
    {
        $ComplexProduct->getLiteProduct()->translit();
    }

    $rep = $this->_RM->getRepository(NewComplexProduct::class);

    $rep->update($ComplexProduct);
    $rep->doUpdate();

    $this->renderComplexProductJSON($ComplexProduct);
}

/**
 * добавляет новый NewComplexProduct в БД
 */
public function actionSaveAsNewComplexProduct()
{
    $json = file_get_contents('php://input');

    $ComplexProduct = new NewComplexProduct($this->DIC->get(TRMEventManager::class));

    // инициализируем объект из массива, полученного из JSON
    $ComplexProduct->initializeFromArray( json_decode($json, true) );

    $ComplexProduct->resetId();
    // сбрасываем все ID у дочерних рисунков (так реализовано)...
    $ComplexProduct["ImagesCollection"]->changeAllValuesFor("images", "id_image2", null);
    $ComplexProduct->getLiteProduct()->translit();
    $ComplexProduct->getLiteProduct()->validate();
//    $ComplexProduct->getChildCollection("ImagesCollection")->changeAllValuesFor("images", "id_image2", null);

    $rep = $this->_RM->getRepository(NewComplexProduct::class);

    $rep->insert($ComplexProduct);
    $rep->doInsert();

    $this->renderComplexProductJSON($ComplexProduct);
}

/**
 * копирует список отсутсвующих характеристик из родительской группы в товар
 */
public function actionCopyFeaturesFromGroup()
{
    $GroupAndProductIdsJSONId = file_get_contents('php://input');
    $Arr = json_decode($GroupAndProductIdsJSONId, true);
    $ProductId = $Arr[1];

    $GroupFeaturesRep = $this->_RM->getRepository(NewGroupFeature::class); // new NewGroupFeatureRepository();
    $ProductFeaturesRep = $this->_RM->getRepository(NewProductFeature::class); // new NewProductFeatureRepository();
    
    $GroupFeaturesCollection = $GroupFeaturesRep->getBy("groupfeature", "ID_Group", $Arr[0]);

    $ProductFeaturesCollection = $ProductFeaturesRep->getBy( "goodsfeatures", "ID_Price", $ProductId );
    if( !$ProductFeaturesCollection )
    {
        $ProductFeaturesCollection = new TRMTypedCollection(NewProductFeature::class);
    }
    
    foreach( $GroupFeaturesCollection as $GroupFeature )
    {
        $EnadleFlag = false;
        foreach($ProductFeaturesCollection as $ProductFeature )
        {
            if( $ProductFeature->getData("features", "ID_Feature") 
                == $GroupFeature->getData("features", "ID_Feature") 
            )
            {
                $EnadleFlag = true;
                break;
            }
        }
        // если характеристика есть и в той и в другой коллекции,
        if( $EnadleFlag ) 
        {
            // при этом она не установлена в продукте,
            // то копируем в товар из группы
            // цикл прерван, поэтому в $ProductFeature данные с найденной характеристикой
            if( !$ProductFeature->getData("goodsfeatures", "FeaturesValue") )
            {
                $ProductFeatureArr = 
                        $this->copyFromGroupToProductFeature($GroupFeature->getRow("groupfeature") );
                $ProductFeatureArr["ID_Price"] = $ProductId;
                $ProductFeature->setRow( "goodsfeatures", $ProductFeatureArr );
            }
            // продолжаем цикл не создавая новую характеристику в коллекции для продукта
            continue;
        }

        // если характеристики из группы нет в коллекции для товара, то копируем
        $TmpProductFeature = new NewProductFeature();
        $TmpProductFeature->setRow( "features", $GroupFeature->getRow("features") );
        
        $ProductFeatureArr = 
                $this->copyFromGroupToProductFeature($GroupFeature->getRow("groupfeature") );
        $ProductFeatureArr["ID_Price"] = $ProductId;
        
        $TmpProductFeature->setRow( "goodsfeatures", $ProductFeatureArr );
        $ProductFeaturesCollection->addDataObject($TmpProductFeature);
    }
    
    echo json_encode( $ProductFeaturesCollection );
}

/**
 * вспомогательная функция,
 * из массива с характеристикой группы создает массив с характеристикой товара,
 * БЕЗ ID-товара
 * 
 * @param array $GroupFeature
 * @return array
 */
private function copyFromGroupToProductFeature( array $GroupFeature )
{
    $ProductFeature["ID_Feature"] = $GroupFeature["ID_Feature"];
    $ProductFeature["FeaturesValue"] = $GroupFeature["FeatureValue"];
    $ProductFeature["Comment"] = $GroupFeature["Comment"];
    
    return $ProductFeature;
}

/**
 * формирует массив в виде JSON-строки 
 * производителей, валют или единицы измерения, в зависимости от значения POST-запроса
 * 
 * @throws TRMException
 */
public function actionGetList()
{
    $ListName = file_get_contents('php://input');
    
    switch ($ListName)
    {
        case "unit" : 
            //$Commonlist->setIdFieldName(NewUnit::getIdFieldName());
            //$Commonlist->setTitleFieldName( array("unit", "UnitShort") );
            //$Commonlist->initializeFromCollection( $this->_RM->getRepository(NewUnit::class)->getAll() );
			
			$Commonlist = $this->_RM->getRepository(NewUnit::class)->getAll();
            break;
        case "valuta" : 
			$Commonlist = new NewCommonList;
            foreach( TRMValuta::$Valutas as $Id => $ValutaData )
            {
				$Commonlist->push( array("ID_valuta" => $Id, "ValutaShort" => $ValutaData["str"][0]) );
//                $Commonlist->setRow($Id, $ValutaData["str"][0]);
            }
            break;
        case "vendors" : 
            // $Commonlist->setIdFieldName(NewVendor::getIdFieldName());
            // $Commonlist->setTitleFieldName( array("vendors", "VendorName") );
            // $Commonlist->initializeFromCollection( $this->_RM->getRepository(NewVendor::class)->getAll() );
			
			$Commonlist = $this->_RM->getRepository(NewVendor::class)->getAll();
            break;
        default: throw new TRMException( __METHOD__ . " Обработчик не найден для {$ListName}", 503);
    }

    echo json_encode($Commonlist);
}


} // NewAjaxProductController
