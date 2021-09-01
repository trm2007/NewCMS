<?php

namespace NewCMS\Libs;

use TRMEngine\File\TRMFile;
use TRMEngine\Helpers\TRMLib;

/**
 * простой класс для работы с курсами валют и пересчета,
 * все расчеты относительно курса рубля
 *
 * @author TRM
 */
class TRMValuta // extends TRMConfigSingleton
{
/**
 * @var array - массив соответсвия внутренного номера валюты, сокращению, коду на сайте центробанка - cbr.ru,
 * а так же массиву строк с возможными сокращениями
 */
public static $Valutas = array(
    "1" => array("RUB", "str" => array( "руб", "rub", "р." ) ),
    "2" => array("EUR", "CBRCode" => "R01239", "str" => array( "евро", "eur", "euro" ) ),
    "3" => array("USD", "CBRCode" => "R01235", "str" => array( "usd", "dollar", "доллар", "$" ) ),
);

/*
	use TRMSingleton;
	use TRMConfigSingleton;
*/

/**
 * @var TRMValuta - экземпляр данного объекта Singleton
 */
protected static $Instance = null;

/**
 * возвращает экземпляр данного класса, если он еще не создан, то создает его
 * @return self - экземпляр данного класса
 */
public static function getInstance()
{
    if( !isset(static::$Instance) )
    {
        $ClassName = get_called_class();
        static::$Instance = new $ClassName();
    }

    return static::$Instance;
}
/**
 * @var array - конфигурационные данные
 */
protected static $ConfigArray = array();

/**
 * загружает конфигурационные данные из файла $filename - должны возвращаться в виде массива
 *
 * @param string - имя файла с конфигурацией
 */
public static function setConfig1( $filename )
{
    if( !is_file($filename) )
    {
            TRMLib::dp( __METHOD__ . " Файл с настройками получить на удалось [{$filename}]!" );
            return false;
    }
    self::$ConfigArray = require_once($filename);
    
    if( defined("ROOT") )
    {
        self::$ConfigArray["CourseFileName"] = ROOT . "/" . ltrim(self::$ConfigArray["CourseFileName"], "/\\");
    }

    if( !is_array(self::$ConfigArray) || empty(self::$ConfigArray) )
    {
            TRMLib::dp( __METHOD__ . " Файл конфигурации вернул неверный формат данных [{$filename}]!" );
            return false;
    }

    return true;
}






static $Valuta = array(); // массив с курсами
static $DateCourse; // дата последнего обновления файла с курсами
static $CursCBcoeff;
/**
 *
 * @var string - индекс для валюты по умолчанию, например RUB как в БД
 */
static $DefaultValuta = "RUB";


protected function __construct()
{
    if( !file_exists(self::$ConfigArray["CourseFileName"]) )
    {
        static::createCourseFile( self::$ConfigArray["CourseFileName"] );
    }

    $f=fopen(self::$ConfigArray["CourseFileName"], "r");
    foreach( static::$Valutas as $index => $val )
    {
        self::$Valuta[$index] = floatval(fgets($f)); // очередная строка из файла
        self::$Valuta[static::$Valutas[$index][0]] = self::$Valuta[$index];

    }

    self::$DateCourse = fgets($f); // последняя строка - время обновления
    fclose($f);
}

/**
 * загружает конфигурационные данные и получает курсы валют из заранее сформированного текстового файла
 *
 * @param string - имя файла с конфигурацией
 */
public static function setConfig( $filename )
{
    if( !self::setConfig1( $filename ) )
    {
        return false;
    }

    if( !isset(self::$ConfigArray["CourseFileName"]) || self::$ConfigArray["CourseFileName"] == "" )
    {
        TRMLib::sp( __METHOD__ . " В конфигурации не задан путь к курсам валют!" );
        return false;
    }
    if( !file_exists(self::$ConfigArray["CourseFileName"]) )
    {
        static::createCourseFile( self::$ConfigArray["CourseFileName"] );
    }
    if( !is_file(self::$ConfigArray["CourseFileName"]) )
    {
        TRMLib::sp( __METHOD__ . " Файл с курсами валют получить на удалось [".self::$ConfigArray["CourseFileName"]."]!" );
        return false;		
    }

    self::$CursCBcoeff = self::$ConfigArray["CursCBcoeff"];

    return true;
}

/**
 * Получаем курс валюты с сайта ЦБР через XML
 * 
 * @param string $valcode - код валюты в центробанке,
 * R01239 - EURO,
 * R01235 - USD
 * @param integer $attempts - количество попыток - повторов в случае неудачи, поумолчанию = 10...
 * 
 * @return double - в случае успеха вернет курс валюты
 * @throws \Exception - если после $attempts попыток не удалось получить курс валюты, то выбрасывается исключение
 */
public static function getXMLCourseFromCB($valcode, $attempts = 10)
{
    $xmldata = null;
    $values = array();

    for( $i=0; $i < $attempts; $i++ )
    {
        $ddate=date("d/m/Y", (strtotime ("now")-($i*60*60*24)) );

        $xmldata=file( "http://www.cbr.ru/scripts/XML_dynamic.asp?date_req1={$ddate}&date_req2={$ddate}&VAL_NM_RQ={$valcode}" );
        if( !$xmldata ) { continue; }

        $data = implode("",$xmldata);
        $parser = xml_parser_create();

        xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
        xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,1);
        xml_parse_into_struct( $parser, $data, $values );
        xml_parser_free($parser);

        if(isset($values[3]["value"]))
        {
            return floatval( str_replace(",", ".", $values[3]["value"]) );
        }
    }
    throw new \Exception("Не удалось получить курсы валют!");
}

/**
 * создает файл с курсами валют
 * 
 * @param string $coursefilename - имя файла
 */
public static function createCourseFile($coursefilename)
{
    $f = new TRMFile($coursefilename);
    
    foreach( static::$Valutas as $index => $val )
    {
        if( !isset( $val["CBRCode"] ) ){ $f->addToBuffer( "1\n"); }
        else { $f->addToBuffer( static::getXMLCourseFromCB( $val["CBRCode"]) . "\n" ); }
    } 
    $f->addToBuffer(time()."\n");
    $f->putBufferTo();
    $f->clearBuffer();
}

/**
 * Получаем курс ЕВРО из файла
 *
 * @return double - Курс рубля к Евро
 */
protected static function GetEUROcourse()
{ 
    return self::$Valuta["EUR"];
}

/**
 * Получаем курс доллара из файла
 *
 * @return double - Курс рубля к доллару
 */
protected static function GetUSDcourse()
{
    return self::$Valuta["USD"];
}

/**
 * Получает дату , на которую актуальны курсы валют в файле
 *
 * @return long - время в секундах (в UNIX) последнего обновления курсов валют
 */
protected static function GetDATEcourse()
{
	return intval(self::$DateCourse);
}

/**
 * возвращает цену в рублях по отношению к цене в запрашиваемой  валюте
 *
 * @param double - цена в запрашиваемой валюте
 * @param int|string - валюта, из которой нужно перевести в рубли
 *
 * @return double - цена в рублях для запрашиваемой суммы в валюте
 */
public static function getPriceInRUB( $price, $valuta )
{
    $valuta = strtoupper(trim(strval($valuta)));
    foreach( static::$Valuta as $k => $course )
    {
        if( $valuta == $k )
        {
            return ($price * $course);
        }
    }
    // если соответсвия не нйдено, то вернется начальное значение цены
    return $price;
}

/**
 * перевод из одной валюты в другую через курс по отношению к рублю
 *
 * @param double $price - цена в запрашиваемой валюте
 * @param string $valutafrom - валюта, из которой нужно перевести сумму $price
 * @param string $valutato - валюта, в которую нужно перевести сумму $price
 
 * @return double - цена в рублях для запрашиваемой суммы в валюте
 */
public static function convert( $price, $valutafrom, $valutato = null )
{
    if( empty($valutato) ) { $valutato = self::$DefaultValuta; }
    if( empty($valutafrom) ) { return $price; }
    if( $valutafrom == $valutato ) { return $price; }

    $valutafrom = strtoupper(trim(strval($valutafrom)));
    $valutato = strtoupper(trim(strval($valutato)));

    if( !array_key_exists($valutafrom, self::$Valuta) )
    {
        $valutafrom = self::$DefaultValuta;
    }
    if( !array_key_exists($valutato, self::$Valuta) )
    {
        $valutato = self::$DefaultValuta;
    }

    return floatval($price) * floatval(self::$Valuta[$valutafrom]) / floatval(self::$Valuta[$valutato]);
}

/**
 * пытается определить тип валюты по ее наименованию или индексу - $str - переданном пользователем
 * 
 * @param string $str - обозначние валюты RUB, USD, или индекс в массиве Valutas
 * 
 * @return int|boolean - если валюта есть в массиве, то возвращается ее индекс, 
 * он же используется в БД, если валюта не нейдена, то вернется false
 */
public static function getValutaIndex($str)
{
    $haystack = strtolower($str);
    foreach (self::$Valutas as $key => $val )
    {
        if(intval($str) == intval($key)) { return $key; }
        foreach( $val["str"] as $needle )
        {
            if( strpos($haystack, $needle) !== false ) { return $key; }
        }
    }
    
    return false;
}

/**
 * @return string - возвращает имя файла, в котором хранятся курсы валют
 */
public static function getCourseFileName()
{
    return self::$ConfigArray["CourseFileName"];
}


} // TRMValuta
