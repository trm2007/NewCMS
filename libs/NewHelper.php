<?php

namespace NewCMS\Libs;

use NewCMS\Domain\Exceptions\NewComplectWrongQueryException;
use NewCMS\Domain\Exceptions\NewComplectZeroPartPriceException;
use TRMEngine\DataArray\TRMDataArray;
use TRMEngine\DataObject\Interfaces\TRMDataObjectInterface;
use TRMEngine\TRMDBObject;

/**
 * класс вспомогательных функций
 *
 */
class NewHelper
{
/**
 * рекурсивная функция для вычисления суммарной цены,
 * рекурсивный запрос к БД для каждого товара из комплекта
 *
 * @param TRMDBObject $DBO
 * @param array $startrow - массив, со значением ID-товара, его price0 и валюта valuta - после запроса к БД 
 *
 * @return double - возвращает вычисленную рекурсивно цену в валюте установленной по умолчанию (рубли)
 */
public static function recursivePrice( TRMDBObject $DBO, $startrow )
{
    //получаем все товары, 
    //которые в таблице комплекта соответствуют данному товару с ID = $startrow["ID_Price"], 
    //который в текущий момент является комплектом
    $query = "
SELECT  `complect`.* , `table1`.`price0` , `table1`.`valuta` 
FROM `complect`, `table1` 
WHERE `table1`.`ID_price` = `complect`.`ID_Price` 
AND `ID_Complect` =" . $startrow["ID_Price"];

    $price0 = 0;
    $result = $DBO->query($query);

    if( !$result )
    {
        throw new NewComplectWrongQueryException($query);
    }
    // если в базе данных нет записей о входящих в комплект товарах, 
    // т.е. товар не является комплектом, 
    // то возвращаем его собственную цену в исходной валюте
    if( $result->num_rows <=0 )
    {
        // так же проверим, если не установлена цена товара, 
        // то считать стоимость комплетка не имеет смысла
        // выбрасывается исключение
        if( empty($startrow["price0"]) )
        {
            throw new NewComplectZeroPartPriceException($startrow["ID_Price"]);
        }
//        return $startrow["price0"];
        return TRMValuta::convert( $startrow["price0"], $startrow["valuta"] );
    } 

    while( $row = $result->fetch_array( MYSQLI_ASSOC ) )
    {
        // для комплекта все считается в рублях
//        $price0 += $row["ComplectCoeff"] 
//                * TRMValuta::convert( self::recursivePrice( $row ), $row["valuta"] ); // реализуем только сложение
        $price0 += $row["ComplectCoeff"] * self::recursivePrice( $DBO, $row ); // реализуем только сложение
    }
    $result->free();

    return $price0;
}

/**
 * вычисляет цену на основе начальной и добавочных процентов
 * 
 * @param double $price0 - начальная цена
 * @param double $percent1 - процент надбавки 0-100
 * @param double $percent2 - ...
 * @param double $percent3 - ...
 * 
 * @return array - массив с новыми ценами, в элементе с индексом "0" начальная цена, в "1" - с прибавкой процента1 и т.д.
 */
static public function calculatePrice($price0, $percent1, $percent2 = 0, $percent3 =0 )
{
    $pricearray = array();
    
    //вычисляем сколько знаков после запятой
    $dig = ($price0 > 1000) ? 0 : ( $price0 <100 ? 2 : 1) ;
    $pricearray[0] = $price0;
    $pricearray[1] = round( $price0*(100+$percent1)/100, $dig, PHP_ROUND_HALF_UP );
    $pricearray[2] = round( $price0*(100+$percent2)/100, $dig, PHP_ROUND_HALF_UP );
    $pricearray[3] = round( $price0*(100+$percent3)/100, $dig, PHP_ROUND_HALF_UP );
    
    return $pricearray;
}

/**
 * устанавливает у объекта поля Price1, Price2, Price3,
 * расчет производится функцией NewHelper::calculatePrice
 * на основе базовой переданной цены $Price0
 * 
 * @param TRMDataObjectInterface $Product - объект продукта, для которого рассчитываются наценки 
 * @param double $Price0 - базовая цена
 */
static public function setPrices(TRMDataObjectInterface $Product, $Price0)
{
    $prarr = static::calculatePrice($Price0, 
            $Product->getData("table1", "pr1"),
            $Product->getData("table1", "pr2"),
            $Product->getData("table1", "pr3") );
    $Product->setData("table1", "Price1", $prarr[1] );
    $Product->setData("table1", "Price2", $prarr[2] );
    $Product->setData("table1", "Price3", $prarr[3] );
}

/**
 * соибирает номера всех записей, у которых поле для родительского элемента имеет имя $ParentFieldName,
 * далее, рекурсивно вызывается для каждого найденного, тем самым собирая дочерние элементы дочерних элементов
 * результирующие (возвращенные) массивы соединяются в один, 
 * проходит все дерево вниз от заданной StartId
 * 
 * @param TRMDBObject $DBO
 * @param string $StartId - первый ID, от которого начинается выборка по дереву, 
 * может быть строкой с перечисленными через запятую значениями
 * @param string $TableName - таблица, из которой производится выборка
 * @param string $IdFieldName - имя поля с ID записей
 * @param string $ParentFieldName - имя поля, которое соержти дочернее ID
 * @param string $OrderFieldName - имя поля, по которому производится сортировка (ВНИМАНИЕ! сортировка в рамках выборки одного ID)
 * @param string $PresentFieldName - если не null, тогда слжержит имя поля-флага, которое проверятся на наличие данных,
 * для добавления к результирующему массиву этой записи ее поле $PresentFieldName должно быть обязательно не пустым, оличным от 0, и не NULL
 * @param boolean $first - при пользовательском вызове этот флаг по умолчанию = true, т.е. первый рекурсивный вызов,
 * он позволяет добавить передаваемый $StartId в первый элемент результирующего массива
 * 
 * @return TRMDataArray - одномерный массив всех дочерних ID из поля $IdFieldName
 */
public static function getAllChildsArray(TRMDBObject $DBO, $StartId, $TableName, $IdFieldName, $ParentFieldName, $OrderFieldName = null, $PresentFieldName = null, $first=true)
{
    $allgroups = new TRMDataArray();
    if( empty($StartId) ) { return $allgroups; }
    
    $query  = "SELECT {$IdFieldName} FROM `{$TableName}` WHERE `{$ParentFieldName}` IN (". addcslashes($StartId,"'").")";
    if( isset($PresentFieldName) )
    {
        $query .=" AND `{$TableName}`.`{$PresentFieldName}`<>''  "
                . "AND `{$TableName}`.`{$PresentFieldName}`<>'0' "
                . "AND `{$TableName}`.`{$PresentFieldName}`<>'NULL'";
    }
    if( isset($OrderFieldName) )
    {
        $query .=" ORDER BY `{$TableName}`.`{$OrderFieldName}` ";
    }
    
//    if($first === true) { $allgroups[] = $StartId; }
    if($first === true) { $allgroups->mergeDataArray( explode(",",$StartId) ); }
    $result1 = $DBO->query($query);
    if( !$result1 )
    {
        //TRMLib::dp( __METHOD__ . "Запрос неудачный [{$query}]" );
        return null;
    }
    if( $result1->num_rows == 0 )
    {
        return $allgroups;
    }

    $CurrentSubGroupsArr = array();
    // добавляем каждый дочерний элемент в массив (если его там нет) и рекурсивно вызываем для него эту функцию
    while ($row1 = $result1->fetch_array(MYSQLI_ASSOC)) 
    {
        // если очередной ID уже есть в массиве, значит в структуре зацикливание, пропускаем его
        if( $allgroups->inArray( $row1[$IdFieldName] ) ) { continue; }
        $allgroups[] = $CurrentSubGroupsArr[] = $row1[$IdFieldName];
    }

//    $CurrentGroups = static::getAllChildsArray($DBO, $row1[$IdFieldName], $TableName, $IdFieldName, $ParentFieldName, $OrderFieldName, $PresentFieldName, false);
    $CurrentGroups = static::getAllChildsArray($DBO, implode(",", $CurrentSubGroupsArr), $TableName, $IdFieldName, $ParentFieldName, $OrderFieldName, $PresentFieldName, false);
    if( !empty($CurrentGroups) )
    {
        $allgroups->mergeDataArrayObject($CurrentGroups);
    }

    $result1->free();
    return $allgroups;
}


//*************** НАДО ПОДУМАТЬ ГДЕ РЕАЛИЗОВЫВАТЬ ЭТОТ МЕТОД ************************
/**
 * функция, которая формирует список для перелинковки
 *
 * @param TRMDBObject $DBO
 * @param int $CountOfVisualElement - кол-во товаров в списке
 * @param int $CurrentId - номер товара, с которым предполагается выводить список
 * @param int $groupnumber - номер группы, для которой формируется список,
 * если не задан производится выборка по всей базе товаров
 *
 * @return array - массив с ID товаров
 */
public static function createLinkRows(TRMDBObject $DBO, $CountOfVisualElement, $CurrentId, $groupnumber=null)
{
    $MaxIdQuery = "SELECT MAX(`ID_price`) FROM `table1`";
    $result = $DBO->query($MaxIdQuery);
    if(!$result || !$result->num_rows ) { return null; }
    $row= $result->fetch_array(MYSQLI_NUM);

    $MaxID = $row[0];

    $query = "SELECT `ID_price`, 
case when `ID_price`<{$CurrentId} Then (`ID_price`+{$MaxID}) else `ID_price` end as `NewId` 
FROM `table1` 
WHERE (case when `ID_price`<{$CurrentId} Then (`ID_price`+{$MaxID}) else `ID_price` end)>{$CurrentId} AND `present`=1 ";
    if( is_numeric($groupnumber) )
    {
        $query .= "AND `Group`={$groupnumber} ";
    }
    $query .= "ORDER BY `NewId` LIMIT {$CountOfVisualElement}";

    $result = $DBO->query($query);
    if(!$result || !$result->num_rows ) { return null; }
    $rows= $DBO->fetchAll($result, MYSQLI_NUM); // $result->fetch_all(MYSQLI_NUM);

    $IdsArr = array();
    foreach($rows as $row){ $IdsArr[] = $row[0]; }

    return $IdsArr;
}


} // NewHelper