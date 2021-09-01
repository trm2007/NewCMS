<?php

namespace NewCMS\Libs;

use TRMEngine\Exceptions\TRMObjectCreateException;
use TRMEngine\Helpers\TRMState;
use TRMEngine\TRMDBObject;

/**
 *  класс для создания SiteMap-ов
 */
class NewSiteMap extends TRMState
{
/**
 * протокол по умолчанию, если не задан в пути глобальных настроек
 */
const DEFAULT_PROTOCOL = "http";
/**
 * @var string - строка с общим SiteMap - Index SiteMap
 */
protected $SiteMapIndex = "";
/**
 * @var array - массив со всеми SiteMaps
 */
protected $SiteMaps = array();
/**
 * @var array - массив с именами файлов, содержит полные URL, включая http://
 */
public $FileNames = array();

/**
 * @var array - список исключений для адресов, которые будут подставляться напрямую
 * после адреса сайта без префиксов, например /potolki без /articlrs/potolki.
 * В дальнейшем нужно реализовать через конфигурцию SiteMap
 */
public $LocExceptions = array();

/**
 * @var string - протокол http или https
 */
static protected $Protocol = "";
/**
 * @var string - адрес сайта, без http и строки запроса, например www.site.ru
 */
static protected $CommonURL = "";
/**
 *
 * @var string имя индексного файла карты сайта, по умолчанию - sitemapindex.xml
 */
static public $SiteMapIndexFileName = "sitemapindex.xml";
/**
 * @var TRMDBObject 
 */
protected $DBO;

public function __construct( TRMDBObject $DBO, array $Config = array() )
{
    if( isset($Config["Exceptions"]) && !empty($Config["Exceptions"]) )
    {
        $this->LocExceptions = $Config["Exceptions"];
    }
    self::$CommonURL = filter_var($_SERVER["SERVER_NAME"], FILTER_SANITIZE_URL);
    if( empty(self::$CommonURL) )
    {
        throw new TRMObjectCreateException("Не задан CommonURL, необходимый для создания объекта NewSiteMap!");
    }

    if( key_exists("HTTP_X_REQUEST_SCHEME", $_SERVER) )
    {
        self::$Protocol = filter_var($_SERVER["HTTP_X_REQUEST_SCHEME"], FILTER_SANITIZE_STRING); // $_SERVER["HTTP_X_REQUEST_SCHEME"];
    }
    else if( key_exists("REQUEST_SCHEME", $_SERVER) )
    {
        self::$Protocol = filter_var($_SERVER["REQUEST_SCHEME"], FILTER_SANITIZE_STRING); // $_SERVER["REQUEST_SCHEME"];
    }
    else
    {
        self::$Protocol = self::DEFAULT_PROTOCOL;
    }
    self::$Protocol .= "://";
    
    $this->DBO = $DBO;
}


/**
 * @return string - протокол добавляемый к адресу "http" или "https"
 */
static function getProtocol()
{
    return self::$Protocol;
}
/**
 * @param string $Protocol - протокол добавляемый к адресу "http" или "https"
 */
static function setProtocol($Protocol)
{
    self::$Protocol = $Protocol;
}

/**
 * формирует содержимое SiteMaps для главной части из меню
 * 
 * @param string $priority - численное значние от 0 до 1 в виде строки, обозначающее приоритет ссылок,
 * по умолчанию = 0.8
 * 
 * @return boolean
 */
public function generateSiteMapsMain($priority = "0.8")
{
    $result = $this->DBO->query("SELECT MenuLink FROM `menu` WHERE `Present`=1");

    if(!$result || $result->num_rows <=0 )
    { $this->addStateString("Не удалось загрузить данные из БД для Main SiteMap!"); return false; }

    $this->SiteMaps["Main"] = '<?xml version="1.0" encoding="UTF-8"?>';
    $this->SiteMaps["Main"] .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.PHP_EOL;

    while ($row = $result->fetch_array(MYSQLI_ASSOC) )
    {
        $this->SiteMaps["Main"] .= "<url>";
        $row['MenuLink'] = ltrim($row['MenuLink'], " /");

        $this->SiteMaps["Main"] .= "<loc>".self::$Protocol . (self::$CommonURL."/".$row['MenuLink']) . "</loc>"; //
        $this->SiteMaps["Main"] .= "<lastmod>".date("Y-m-d")."</lastmod>";

        $changefreq = null;
        //если это раздел новостей или статей, то они могут обновляться часто
        if( strpos($row["MenuLink"], "news") || strpos($row["MenuLink"], "articles") ) { $changefreq = "daily"; }
        else { $changefreq = "weekly"; }

        $this->SiteMaps["Main"] .= "<changefreq>".$changefreq."</changefreq>";
        $this->SiteMaps["Main"] .= "<priority>".$priority."</priority>";

        $this->SiteMaps["Main"] .= "</url>" . PHP_EOL;
    }
    $this->SiteMaps["Main"] .= "</urlset>";

    return true;
}

/**
 * генерируем карту статей и прочих документов
 * 
 * @param string $priority - по умолчанию для этих ссылок 0.5
 * @param string $changefreq - по умолчанию = yearly
 * 
 * @return boolean
 */
public function generateSiteMapsArticles($priority = "0.5", $changefreq = "yearly")
{
    $articlesListPrefix = trim(\GlobalConfig::$ConfigArray["articlesListPrefix"], "\//");

    $result = $this->DBO->query("
        SELECT `ArticleURL`, `ArticlesURL` 
        FROM `articles`, `articlestype` 
        WHERE `articles`.`Reserv`=`articlestype`.`ID_articlestype` 
        AND (`articles`.`onlyowner`=1 OR `articles`.`onlyowner`=0)
    ");

    if(!$result || $result->num_rows <=0 )
    { $this->addStateString("Не удалось загрузить данные из БД для Articles SiteMap!"); return false; }

    $this->SiteMaps["Articles"] = '<?xml version="1.0" encoding="UTF-8"?>';
    $this->SiteMaps["Articles"] .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.PHP_EOL;
    while ($row = $result->fetch_array(MYSQLI_ASSOC) )
    {
        // если текущий адрес документов есть в массиве исключений,
        // т.е. добавляется к адресу сайта напрямую без префикса, 
        // то формируем соответсвующий префикс для текущего документа
        if( in_array($row['ArticlesURL'], $this->LocExceptions) )
        {
            $CurrentPrefix = $row['ArticlesURL'];
        }
        // иначе текущий префикс включает общий префикс для документов
        else
        {
            $CurrentPrefix = $articlesListPrefix . "/" . $row['ArticlesURL'];
        }
        $this->SiteMaps["Articles"] .= "<url>";
        $this->SiteMaps["Articles"] .= "<loc>"
                .self::$Protocol
                .self::$CommonURL . "/" . $CurrentPrefix . "/" . $row['ArticleURL']
                ."</loc>"; //
        $this->SiteMaps["Articles"] .= "<changefreq>".$changefreq."</changefreq>";
        $this->SiteMaps["Articles"] .= "<priority>".$priority."</priority>";
        $this->SiteMaps["Articles"] .= "</url>" . PHP_EOL;
    }
    $this->SiteMaps["Articles"] .= "</urlset>";

    return true;
}

/**
 * генерируем карту групп товаров
 * 
 * @param string $priority - по умолчанию для этих ссылок 0.5
 * @param string $changefreq - по умолчанию = yearly
 * 
 * @return boolean
 */
public function generateSiteMapsGroup($priority = "0.6", $changefreq = "yearly")
{
    $pricePrefix = \GlobalConfig::$ConfigArray["pricePrefix"];

    $result = $this->DBO->query("SELECT GroupTranslit FROM `group` WHERE `GroupPresent`=1");

    if(!$result || $result->num_rows <=0 )
    { $this->addStateString("Не удалось загрузить данные из БД для Group SiteMap!"); return false; }

    $this->SiteMaps["Group"] = '<?xml version="1.0" encoding="UTF-8"?>';
    $this->SiteMaps["Group"] .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.PHP_EOL;
    while ($row = $result->fetch_array(MYSQLI_ASSOC) )
    {
        $this->SiteMaps["Group"] .= "<url>";
        $this->SiteMaps["Group"] .= "<loc>".self::$Protocol.str_replace("//", "/", (self::$CommonURL."/".$pricePrefix."/".$row['GroupTranslit']) )."</loc>"; //
        $this->SiteMaps["Group"] .= "<changefreq>".$changefreq."</changefreq>";
        $this->SiteMaps["Group"] .= "<priority>".$priority."</priority>";
        $this->SiteMaps["Group"] .= "</url>" . PHP_EOL;
    }
    $this->SiteMaps["Group"] .= "</urlset>";

    return true;
}

/**
 * генерируем карту товаров
 * 
 * @return boolean
 */
public function generateSiteMapsPrice()
{
    $catalogPrefix = \GlobalConfig::$ConfigArray["catalogPrefix"];

    //получим самый посещаемый товар из БД
    $result = $this->DBO->query("SELECT MAX(Visits) FROM `table1` WHERE `present`=1");
    if(!$result || $result->num_rows <=0 )
    { $this->addStateString("Не удалось получить количество визитов из БД для Price SiteMap!"); return false; }
    $row = $result->fetch_array(MYSQLI_NUM);
    $MaxVisits = $row[0]>0 ? floatval($row[0]) : 1;

    $result = $this->DBO->query("SELECT PriceTranslit, Visits FROM `table1` WHERE `present`=1 ORDER BY Visits DESC");

    if(!$result || $result->num_rows <=0 )
    { $this->addStateString("Не удалось загрузить данные из БД для Price SiteMap!"); return false; }

    $this->SiteMaps["Price"] = '<?xml version="1.0" encoding="UTF-8"?>';
    $this->SiteMaps["Price"] .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.PHP_EOL;
    while ($row = $result->fetch_array(MYSQLI_ASSOC) )
    {
        if( isset($row['PriceTranslit']) )
        {
        $this->SiteMaps["Price"] .= "<url>";
        $this->SiteMaps["Price"] .= "<loc>".self::$Protocol.str_replace("//", "/", (self::$CommonURL."/".$catalogPrefix."/".$row['PriceTranslit'])  )."</loc>"; //
        $this->SiteMaps["Price"] .= "<changefreq>daily</changefreq>";
        $priority = round(floatval($row['Visits'])/$MaxVisits*0.4 + 0.6, 2);
        $this->SiteMaps["Price"] .= "<priority>".$priority."</priority>";
        $this->SiteMaps["Price"] .= "</url>" . PHP_EOL;
        }
    }
    $this->SiteMaps["Price"] .= "</urlset>";

    return true;
}
 
/**
 * генерирует все SiteMaps из составных частей,
 * записывает все части SiteMaps в файлы и создает sitemapindex.xml
 * 
 * @return boolean
 */
public function generateAllSiteMaps()
{
    if( !$this->generateSiteMapsMain() ) { return false; }

    if( !$this->generateSiteMapsArticles() ) { return false; }

    if( !$this->generateSiteMapsGroup() ) { return false; }

    if( !$this->generateSiteMapsPrice() ) { return false; }

    $this->SiteMapIndex = '<?xml version="1.0" encoding="UTF-8"?>';
    $this->SiteMapIndex .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.PHP_EOL;
    $this->FileNames = array();
    
    $this->FileNames = array();
    for (reset($this->SiteMaps); ($key = key($this->SiteMaps)); next($this->SiteMaps))
    {
        if(isset($this->SiteMaps[$key]) && strlen($this->SiteMaps[$key])>0)
        {
            $filename = strtolower($key)."sitemap.xml";
            if (false === file_put_contents ( ROOT . "/" . $filename, $this->SiteMaps[$key]) )
            { $this->addStateString("Не могу записать файл SiteMap для ".$key); return false; }	

            $fileURL = self::$Protocol
                    .trim(self::$CommonURL, "/\\")
                    ."/".$filename;
            $this->SiteMapIndex .= "<sitemap>"
                    ."<loc>"
                    .$fileURL
                    ."</loc>"
                    ."</sitemap>" . PHP_EOL;
            $this->FileNames[] = $fileURL;
        }
    }
    $this->SiteMapIndex .= "</sitemapindex>";
    $filename = self::$SiteMapIndexFileName;
    if (false === file_put_contents (ROOT . "/" . $filename, $this->SiteMapIndex) )
    { $this->addStateString("Не могу записать файл SiteMapIndex.XML"); return false; }

    $fileURL = self::$Protocol
                    .trim(self::$CommonURL, "/\\")
                    ."/".$filename;

    $this->FileNames[] = $fileURL;
    
    return true;
}

} // NewSiteMap
