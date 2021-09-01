<?php

namespace NewCMS\Libs;

use NewCMS\Domain\NewLiteProductForCollection;
use NewCMS\Repositories\NewLiteProductForCollectionRepository;
use TRMEngine\DataMapper\TRMDataMapper;
use TRMEngine\DataMapper\TRMSafetyFields;
use TRMEngine\Exceptions\TRMException;
use TRMEngine\File\TRMStringsFile;
use TRMEngine\TRMDBObject;

/**
 * класс для работы с текстовыми прайсам
 * 
 * @author TRM 2018-07-30
 */
class NewPrice
{
/**
 * @var string - имя таблицы
 */
const TABLE_NAME = "table1";
/**
 * @var string - имя вспомогательной таблицы с описанием
 */
const DESCRIPTION_TABLE_NAME = "goodsdescription";

/**
 * @var string - внутренний разделитель полей при формировнии текстового прайса
 */
private $Separator;
/**
 * @var TRMStringsFile - объект для работы с фалами, в данном случае с текстовым прайс-листом
 */
protected $MyFile;
/**
 * @var int - начальная группа, от которого начинается сбор всех товаров по БД
 */
protected $StartGroup;
/**
 * @var TRMSafetyFields - дата маппер для чтения коллекции товаров
 */
protected $SafetyFields;
/**
 * @var NewLiteProductForCollectionRepository 
 */
protected $ProductListRepository;

/**
 * @var array - массив настроек с полями для получения данных, устанавливается в DataMapper ($SafetyFields)
 */
protected $FieldsType = array(
    self::TABLE_NAME => array(
        TRMDataMapper::STATE_INDEX => TRMDataMapper::READ_ONLY_FIELD,
        TRMDataMapper::FIELDS_INDEX => array(
            "ID_price" => array(
                TRMDataMapper::STATE_INDEX => TRMDataMapper::READ_ONLY_FIELD,
                TRMDataMapper::KEY_INDEX => "PRI",
                TRMDataMapper::EXTRA_INDEX => "auto_increment"
            ),
            "Name" => array( TRMDataMapper::STATE_INDEX => TRMDataMapper::READ_ONLY_FIELD ),
            "articul" => array( TRMDataMapper::STATE_INDEX => TRMDataMapper::READ_ONLY_FIELD ),
            "vendor" => array( TRMDataMapper::STATE_INDEX => TRMDataMapper::READ_ONLY_FIELD ),
            "unit" => array( TRMDataMapper::STATE_INDEX => TRMDataMapper::READ_ONLY_FIELD ),
            "valuta" => array( TRMDataMapper::STATE_INDEX => TRMDataMapper::READ_ONLY_FIELD ),
            "Image" => array( TRMDataMapper::STATE_INDEX => TRMDataMapper::READ_ONLY_FIELD ),
            "Group" => array( TRMDataMapper::STATE_INDEX => TRMDataMapper::READ_ONLY_FIELD ),
            "price0" => array( TRMDataMapper::STATE_INDEX => TRMDataMapper::READ_ONLY_FIELD ),
            "pr1" => array( TRMDataMapper::STATE_INDEX => TRMDataMapper::READ_ONLY_FIELD ),
            "pr2" => array( TRMDataMapper::STATE_INDEX => TRMDataMapper::READ_ONLY_FIELD ),
            "pr3" => array( TRMDataMapper::STATE_INDEX => TRMDataMapper::READ_ONLY_FIELD ),
            "Quant" => array( TRMDataMapper::STATE_INDEX => TRMDataMapper::READ_ONLY_FIELD ),
            "MinPart" => array( TRMDataMapper::STATE_INDEX => TRMDataMapper::READ_ONLY_FIELD ),
            "present" => array( TRMDataMapper::STATE_INDEX => TRMDataMapper::READ_ONLY_FIELD ),
            "item_order" => array( TRMDataMapper::STATE_INDEX => TRMDataMapper::READ_ONLY_FIELD ),
            "PriceTranslit" => array( TRMDataMapper::STATE_INDEX => TRMDataMapper::READ_ONLY_FIELD ),
            "Comment" => array( TRMDataMapper::STATE_INDEX => TRMDataMapper::READ_ONLY_FIELD ),
        ),
    ),
    self::DESCRIPTION_TABLE_NAME => array(
        TRMDataMapper::STATE_INDEX => TRMDataMapper::FULL_ACCESS_FIELD,
        TRMDataMapper::FIELDS_INDEX => array(
            "ID_goods" => array(
                TRMDataMapper::STATE_INDEX => TRMDataMapper::FULL_ACCESS_FIELD,
                TRMDataMapper::KEY_INDEX => "PRI",
                TRMDataMapper::EXTRA_INDEX => "auto_increment",
                TRMDataMapper::RELATION_INDEX => array( TRMDataMapper::OBJECT_NAME_INDEX => self::TABLE_NAME, 
                                                           TRMDataMapper::FIELD_NAME_INDEX => "ID_price" ),
            ),
        ),
    ),
);


/**
 * конструктор создает объект списка цен (прайс) привязанного к одной группе 
 * и всем ее дочерним группам.
 * Создается объект списка товаров (данные), 
 * для него получеется из DI-контейнера и связывается репозиторий,
 * а так же создается датамаппер - SafetyFields.
 * 
 * @param int $StartGroup - номер группы, для которой создается список цен
 */
public function __construct($StartGroup, TRMDBObject $DBO, NewLiteProductForCollectionRepository $Rep)
{
    $this->Separator = chr(9);
    $this->MyFile = new TRMStringsFile();
    $this->StartGroup = $StartGroup;

    $this->SafetyFields = new TRMSafetyFields($DBO);
    $this->SafetyFields->setFieldsArray($this->FieldsType);
    $this->ProductListRepository = $Rep;
    $this->ProductListRepository->setCurrentGroupId($this->StartGroup);
    // связываем наш дата маппер (SafetyFields) с репозиторием
    $this->ProductListRepository->setDataMapper($this->SafetyFields);
}

/**
 * функция собирает все товары для групп, 
 * у которых самый верхний родитель имеет ID = $this->StartGroup
 * 
 * @param string $filename - имя файла, куда записывается прайс
 * @return boolean
 */
public function createPriceTxtFromDB($filename)
{
    $ProductsList = $this->ProductListRepository->getAll();

    // получаем названия полей из ключей массива FieldsType,
    // затем объединяем их в строку с использованием $this->Separator,
    // и добавляем в буфер - это будет первая строука в файле прайс-листа
    $this->MyFile->addStringToArray( 
        implode( 
            $this->Separator , 
            array_keys($this->FieldsType[self::TABLE_NAME][TRMDataMapper::FIELDS_INDEX]) 
        ) 
    );

    foreach($ProductsList as $Object) 
    {
        $row = $Object->getRow(self::TABLE_NAME);
        $row['Comment'] = str_replace(chr(13).chr(10),"<br>",$row['Comment']);
        $row['Comment'] = str_replace(chr(10).chr(10),chr(10),$row['Comment']);
        $row['Comment'] = trim( str_replace(chr(10),"<br>",$row['Comment']) );

        $row['price0'] = number_format($row['price0'], 2, ',', '');

        $this->MyFile->addStringToArray( implode( $this->Separator , $row ) ); //$this->generateOneRowToTxt($row) );
    }

    if( !$this->MyFile->openFile($filename, 'w+') )
    {
        throw new TRMException(__METHOD__ . "Не могу открыть файл [" . $this->MyFile->getFullPath() . "]" );
    }

    if( !$this->MyFile->putStringsArrayTo() )
    {
        throw new TRMException(__METHOD__ . " Не могу записать буфер:<br>\n [" . $this->MyFile->getBuffer() . "]");
    }
    
    return true;
}

/**
 * записывает содержимое из файла $filename в БД
 * 
 * @param string $filename - имя файла, содержащего прйс-лист (обычно csv с разделителями табуляция)
 * 
 * @throws TRMException
 */
public function putPriceToDB($filename)
{
    // получаем все строки из файла в массив, 
    // пропуская пустые (настройки поумолчанию функции getEveryStringToArray)
    if ( !$this->MyFile->getEveryStringToArrayFrom($filename) )
    {
        throw new TRMException( __METHOD__ . " Не удалось прочитать прайс-лист из файла {$filename}...");
    }

    $IndexArray = array();
    $FirstFlag = true;
    
    $Count = 0;
    // просматриваем все строки
    foreach( $this->MyFile->getArrayBuffer() as $curstr )
    {
        //разделяем строку используя табуляцию=chr(9) и помещяем результат в массив $row
        $row=explode( $this->Separator, $curstr );
        // очищаем все ячейки в массиве $row от пробелов по краям
        array_walk($row, function (&$item1, $key){
            $item1 = trim($item1);
        });

        // в первой строке содержатся имена полей!!!
        // формируем список полей
        if( $FirstFlag )
        {
            // заполняем данные о доступных для записи полях,
            // изначально в SafetyFields все поля READ_ONLY_FIELD ...
            $IndexArray = $this->generateSafetyFieldsFromRow($row);
            $FirstFlag = false;
            $Count = count($IndexArray);
            continue;
        }

        $CurrentRow = array();

        foreach($IndexArray as $i => $FieldName) // $i=0; $i < $Count; $i++)
        {
            $CurrentRow[ $FieldName ] = isset($row[$i]) ? $row[$i]: "";
        }

        if( isset($CurrentRow["price0"]) )
        {
            $CurrentRow["price0"] = self::getCorrectFloat($CurrentRow["price0"]);
        }
        if( isset($CurrentRow["Quant"]) )
        {
            $CurrentRow["Quant"] = self::getCorrectFloat($CurrentRow["Quant"]);
        }
        if( isset($CurrentRow["MinPart"]) )
        {
            $CurrentRow["MinPart"] = self::getCorrectFloat($CurrentRow["MinPart"]);
        }
        //if( empty($CurrentRow["price0"]) ) { $CurrentRow["price0"]=""; }

        $CurrentProduct = new NewLiteProductForCollection();
        $CurrentProduct->setRow(self::TABLE_NAME, $CurrentRow);
        // связываем данные из таблицы описания!
        // присваиваем ID такое же как у продукта
        $CurrentProduct->setRow(self::DESCRIPTION_TABLE_NAME, array("ID_goods" => $CurrentRow["ID_price"]));

        $this->ProductListRepository->save($CurrentProduct);
    }//foreach

    $this->ProductListRepository->doUpdate();
}

/**
 * устанавливает перечисленные поля в FULL_ACCESS_FIELD
 * 
 * @param array $row - массив с именами полей
 */
protected function generateSafetyFieldsFromRow(array $row)
{
    $IndexArray = array();
    // что бы не вызывать функцию count(...) на каждом шаге выполнения цикла 
    // сохранем значение в отдельную переменную
    $Count = count($row);
    for($i=0; $i < $Count; $i++)
    {
        if( !array_key_exists( $row[$i], $this->FieldsType[self::TABLE_NAME][TRMDataMapper::FIELDS_INDEX] ) )
        {
            continue;
        }
        $IndexArray[$i] = $row[$i];
        $this->SafetyFields->setFieldState( self::TABLE_NAME, $row[$i], TRMDataMapper::FULL_ACCESS_FIELD );
    }
    return $IndexArray;
}

/**
 * из полученной строки (или приведенного к строке значения) 
 * получает корректное значение типа float для php, десятичная точка - знак точки "."
 * с округлением до $decimal-го знака после запятой в большую сторону,
 * по умолчанию округление до 2-го знака...
 * 
 * "12,455,530.643" => 12455530.65 ;
 * "78'543,978" => 78543.98 ;
 * "23 507,679" = > 23507.68 ;
 * 
 * @param string $str - строка с любым числом
 * @param int $decimals - до какого знака после запятой округлять, по умолчанию = 2

 * @return float - возвращает число, округленное до заданного знака - $decimal,
 * если в $str ничего нет или 0, то вернется 0.0
 */
public static function getCorrectFloat( $str, $decimals = 2 )
{
    if( empty($str) ) { return 0.0; }

    $cost = (string)$str;
    $cost = str_replace(" ", "", $cost);
    if( false === strpos($cost, ".") ) { $cost = str_replace(",", ".", $cost); }
    $cost = str_replace(array(","," ","'","`"), "", $cost);

    return round( floatval( $cost ), $decimals );
//    return floatval( number_format( $cost, $decimals, '.', '' ) );
}


} // NewPrice