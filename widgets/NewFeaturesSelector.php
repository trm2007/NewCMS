<?php

namespace NewCMS\Widgets;

use NewCMS\Repositories\NewGroupRepository;
use NewCMS\Views\CMSBaseView;
use TRMEngine\Helpers\TRMLib;
use TRMEngine\TRMDBObject;

/**
 * виджет - выбор параметров товаров
 */
class NewFeaturesSelector
{
/**
 * @var string - имя части URL обозначающее номер страницы для пагинации
 */
protected $PageNumericName = "page";
/**
 * @var int группа товаров для которой будет формироваться список всех возможных характеристик, в том числе для товаров из всех доерних групп
 */
protected $CurrentGroupId = null;
/**
 * @var string Часть адреса URL для заданной группы товаров
 */
protected $CurrentGroupURL = "";
/**
 * @var string строка с ID-номерами главной группы - $CurrentGroupId и все подгрупп через запятую
 */
protected $SubGroupsStr = "";
/**
 * @var string строка с ID-номерами товаров, входящих в основную группу и ее дочерние
 */
protected $ProductsIdStr = "";
/**
 * @var array содержит все возможные характеристики и их значения в БД для товаров в группе $CurrentGroupId
 */
public $FeaturesValsArray = array();
/**
 * @var array список выбранных значений характеристик для данного объекта
 */
public $SelectedFeaturesList = array();
/**
 * @param TRMDBObject $DBO
 */
protected $DBO;

public function __construct(TRMDBObject $DBO)
{
    $this->DBO = $DBO;
    $this->PageNumericName = defined(PAGE_NUMERIC_NAME) ? PAGE_NUMERIC_NAME : "page";
}

/**
 * @return string - имя части URL обозначающее номер страницы для пагинации
 */
public function getPageNumericName()
{
    return $this->PageNumericName;
}
/**
 * @param string $PageNumericName - имя части URL обозначающее номер страницы для пагинации
 */
public function setPageNumericName($PageNumericName)
{
    $this->PageNumericName = $PageNumericName;
}

/**
 * устанавливает группу товаров для которой будет формироваться список всех возможных характеристик, 
 * в том числе для товаров из всех дочрних групп
 * 
 * @param int $GroupId - ID-группы товаров
 * @param string $GroupURL - как имя группы присутсвует в URL-запросе
 */
public function setCurrentGroupId($GroupId, $GroupURL)
{
    $this->CurrentGroupId = intval($GroupId);
    $this->CurrentGroupURL = $GroupURL;
}

/**
 * @return int - ID-группы товаров
 */
public function getCurrentGroupId()
{
    return $this->CurrentGroupId;
}

/**
 * @return string - часть URL для группы товаров
 */
public function getCurrentGroupURL()
{
    return $this->CurrentGroupURL;
}

/**
 * для заданной группы $this->CurrentGroupId возвращает все дочерние
 * 
 * @return array|false
 */
//public function generateSubGroupNums()
//{
//    // проверка, что бы была установлена начальная группа
//    if( !isset($this->CurrentGroupId) ) { return array(); }
//    return (NewHelper::getAllChildsArray(
//        $this->CurrentGroupId, 
//        "group", 
//        "ID_group", 
//        "GroupID_Parent", 
//        "GroupOrder", 
//        "GroupPresent"
//    ))->getDataArray();
//}

/**
 * @return array|false - массив с ID-номерами товаров, входящих в основную группу и ее дочерние
 */
public function generateProductsId()
{
    // проверка выбраны ли все дочерние подгруппы
    if( empty($this->SubGroupsStr) )
    {
//        $this->SubGroupsStr = implode(", ", $this->generateSubGroupNums());
        $this->SubGroupsStr = implode(
            ", ", 
            NewGroupRepository::getSubGroupsIdFromDB(
                $this->DBO,
                $this->CurrentGroupId, 
                true )
            ->getDataArray() 
        );
    }
    $query = "SELECT `ID_price` FROM `table1` "
            . "WHERE `table1`.`Group` IN ({$this->SubGroupsStr}) AND `table1`.`present`=1";
    $result = $this->DBO->query($query);
    if( !$result ) { return array(); }
    
    return $this->DBO->fetchAll($result);
}

/**
 * возвращает все возможные характертистики, 
 * которые встречаются в товарах для группы,
 * или в ее подгруппах
 * 
 * @return array
 */
public function getAllFeaturesForGroupProducts()
{
    // выбираем все ID товаров, которые входят в группу и ее подгруппы - SubGroupsStr
    if( empty($this->ProductsIdStr) )
    {
        $Res = $this->generateProductsId();
        $iterator = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($Res));
        $IdsArr = iterator_to_array($iterator, false);
        $this->ProductsIdStr = implode( ", ", $IdsArr );
    }
    
    $query = "SELECT * FROM `goodsfeatures`, `features` "
            . "WHERE `features`.`ID_Feature` = `goodsfeatures`.`ID_Feature` "
            . "AND `goodsfeatures`.`ID_Price` IN ({$this->ProductsIdStr}) "
            . "GROUP BY `goodsfeatures`.`ID_Feature`";

    $result = $this->DBO->query($query);
    if( !$result ) { return array(); }
    
    return $this->DBO->fetchAll($result); // $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * возвращает массив всех встречаюшихся значений 
 * характеристики с номером $IdFeatures в БД в таблице goodsfeatures (для товаров)
 * 
 * @param int $IdFeature - ID проверяемой характеристики в БД
 * @return array
 */
public function getAllValuesForFeatures($IdFeature)
{
    if( empty($this->ProductsIdStr) )
    {
        $Res = $this->generateProductsId();
        $iterator = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($Res));
        $IdsArr = iterator_to_array($iterator, false);
        $this->ProductsIdStr = implode( ", ", $IdsArr );
    }

    $query = "
SELECT `goodsfeatures`.`ID_Feature` ,  `goodsfeatures`.`FeaturesValue` ,  `features`.`FeatureTitle`, `features`.`FeaturesTranslit` 
FROM  `goodsfeatures` ,  `features` 
WHERE  `features`.`ID_Feature` =  `goodsfeatures`.`ID_Feature` 
AND `features`.`FeaturesComparable` = 1
AND  `goodsfeatures`.`ID_Price` 
IN ({$this->ProductsIdStr})
AND  `features`.`ID_Feature` = {$IdFeature} ";

    $query .= " AND  `FeaturesValue` <>  ''
            GROUP BY `FeaturesValue` 
            ORDER BY `goodsfeatures`.`FeaturesValue`, `ID_Feature`";

    $result = $this->DBO->query($query);
    if( !$result ) { return array(); }
    
    return $this->DBO->fetchAll($result); // $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * генерирует двумерный массив названий характеристик, 
 * встречаюшихся для заданной группы, и их значений из БД
 * 
 * @return array
 */
public function generateFeaturesValsArray()
{
    $this->FeaturesValsArray = array();
    
    $Features = $this->getAllFeaturesForGroupProducts();
    if( empty($Features) ) { return array(); }
    
    foreach($Features as $row)
    {
        $vals = $this->getAllValuesForFeatures($row["ID_Feature"]);
        // если такая характеристика не установлена ни у одного товара,
        // цикл продолжается для новой характеристики из массива $Features
        if( empty($vals) || count($vals)==1 ) { continue; }
        
        $NewFeatures = $row;
        $NewFeatures["Values"] = $vals;
        
        $this->FeaturesValsArray[] = $NewFeatures;
    }

    return $this->FeaturesValsArray;
}

/**
 * @return array|null - возвращает массив вида Features[translit] = array(id, name)
 */
public function getFeaturesTranslitKeyArray()
{
    $query = "SELECT  `ID_Feature` ,  `FeaturesTranslit`, `FeatureTitle` FROM  `features`";
    $result = $this->DBO->query($query);
    if( !$result ) { return null; }

    $FeaturesTranslitKeyArray = array();
    foreach( $this->DBO->fetchAll($result) as $row ) // $result->fetch_all(MYSQLI_ASSOC) as $row )
    {
        $FeaturesTranslitKeyArray[$row["FeaturesTranslit"]] = 
                array( "id" => $row["ID_Feature"], "name" => $row["FeatureTitle"] );
        
    }
    return $FeaturesTranslitKeyArray;
}

/**
 *  формирует массив выбранных характеристик из строки запроса (URL)
 * 
 * @param string $url - URL для анализа
 * @return boolean
 */
public function selectFeaturesFromURL($url)
{
    if( !isset($url) || !strlen($url) ) { return false; }
    //если в строке $url не встречается "-eqv-" значит ни один параметр не задан 
    if( strpos($url, "-eqv-") === false ) { return false; }

    $this->SelectedFeaturesList = array();

    // декодирует адрес URL если есть символы в 4-х байтовой кодировке
//    $url = urldecode($url);

    // все адреса в URL имеют кодировку UTF-8, переводим в кодировку, установленную для сайта
    $url = TRMLib::conv($url, "UTF-8", \GlobalConfig::$ConfigArray["Charset"]);

    //разбиваем строку параметров через / в массив, 
    //что бы получить все установленные характеристики
    $Features = explode('/', $url);

    $page = null;

    // получаем массив вида: $FeaturesTranslitArray[translit] = array(id, name)
    $FeaturesTranslitArray = $this->getFeaturesTranslitKeyArray();

    foreach( $Features as $cur )
    {
        if( strpos($cur, "-eqv-") === false ) { continue; }
        if( strpos($cur, $this->PageNumericName) !== false )
        {
            $page = $cur;
            continue;
        }
        if( strpos($cur, "select") !== false )
        {
            continue;
        }

        // разбиваем текущую строку через -eqv-   название характеристики и значения
        $tmp = explode("-eqv-", $cur);

        if( is_string($tmp[1]) && strpos($tmp[1], "-or-")!==false )
        {
            // если в значениях встречается -or- значит выбрано несколько значений, 
            // разбиваем строку на массив и добавляем каждое значние
            $tmp2 = explode("-or-", $tmp[1]);
            //$StartFlag = true;
            foreach( $tmp2 as $value )
            {
                if(isset($FeaturesTranslitArray[$tmp[0]]) ) //&& $StartFlag)
                {
                    $this->SelectedFeaturesList[] = array( 
                        "id" => $FeaturesTranslitArray[$tmp[0]]["id"], 
                        "name" => $FeaturesTranslitArray[$tmp[0]]["name"], 
                        "value" => urldecode($value) // str_replace("_", ".", $value) 
                    );
                    $StartFlag = false;
                }
            }
        }
        else if( isset( $FeaturesTranslitArray[$tmp[0]] ) )
        {
            $this->SelectedFeaturesList[] = array( 
                "id" => $FeaturesTranslitArray[$tmp[0]]["id"], 
                "name" => $FeaturesTranslitArray[$tmp[0]]["name"], 
                "value" => urldecode($tmp[1]) // str_replace("_", ".", $tmp[1]) 
            );
        }
    }
    
    return true;
}

/**
 * генерирует добавку для заголовка в зависимости от установленных характеристик в URL
 *
 * @param string $url - URL с характеристиками для формирования вспомогательрного заголовка
 * 
 * @return string
 */
public function generateTitleStrFromURL($url)
{
    if( empty($this->SelectedFeaturesList) )
    {
        $this->selectFeaturesFromURL($url);
    }
    if( empty($this->SelectedFeaturesList) ) { return "" ; }
    
    $tmpTitle = "";
    $OldName = "";

    foreach( $this->SelectedFeaturesList as $Feature )
    {
        if( !isset($Feature["name"]) ) { continue; }

        if($Feature["name"] !== $OldName)
        {
            $tmpTitle .= $Feature["name"]." ".$Feature["value"].", ";
        }
        else { $tmpTitle .= " или ".$Feature["value"].", "; }

        $OldName = $Feature["name"];
    }

    return trim($tmpTitle, ", ");
}

/**
 * отображает виджет FeaturesSelector
 * 
 * @param NewFeaturesSelector $FeaturesSelector
 */
static public function render(self $FeaturesSelector)
{
    if( empty($FeaturesSelector->FeaturesValsArray) )
    {
        $FeaturesSelector->generateFeaturesValsArray();
        if( empty($FeaturesSelector->FeaturesValsArray) ) { return; }
    }

    $FSView = new CMSBaseView("selector", null);
    $FSView->setPathToViews( ROOT . TOPIC . '/views/widgets');
    $FSView->setVar("FeaturesSelector", $FeaturesSelector);

    $FSView->render();
}


} // NewFeaturesSelector