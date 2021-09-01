<?php

namespace NewCMS\Controllers\AJAX;

use GlobalConfig;
use NewCMS\Controllers\AJAX\NewAjaxCommonController;
use NewCMS\Domain\NewNews;
use NewCMS\Repositories\NewNewsRepository;
use Symfony\Component\HttpFoundation\Request;
use TRMEngine\DiContainer\TRMDIContainer;
use TRMEngine\File\TRMFile;

/**
 * обработка AJAX-запросов для новостей
 */
class NewAjaxNewsController extends NewAjaxCommonController
{
/**
 * количество последних новостей в XML файле по умолчанию
 */
const NEWS_DEFAULT_COUNT = 50;
/**
 * количество новостей сохраняемых в XML-файле
 * @var int
 */
protected $XMLNewCount = self::NEWS_DEFAULT_COUNT;
/**
 * @var NewNewsRepository
 */
protected $Rep;


public function __construct(Request $Request, TRMDIContainer $DIC)
{
    parent::__construct($Request, $DIC);
    $this->Rep = $this->_RM->getRepository(NewNews::class);
}


/**
 * возвращает объект NewVendor в виде JSON
 */
public function actionGetNew()
{
    $NewId = file_get_contents('php://input');
    
    $New = $this->Rep->getById($NewId);
    
    echo json_encode($New);
}

/**
 * рендерит клиенту JSON объект NewComplexArticle,
 * 
 */
public function actionGetEmptyNew()
{
    echo json_encode( $this->Rep->getNewObject());
}

/**
 * сохраняет NewComplexArticle в БД
 */
public function actionUpdateNew()
{
    $json = file_get_contents('php://input');

    $New = new NewNews();

    // инициализируем объект из массива, полученного из JSON
    $New->initializeFromArray( json_decode($json, true) );

    $this->Rep->update($New);
    $this->Rep->doUpdate();

    echo json_encode($New);
}

public function actionGetNewsList()
{
    echo json_encode( $this->Rep->getAll() );
}

/**
 * генерирует XML файл с новостями
 */
public function actionGenerateNewsXML()
{
    $filename = GlobalConfig::$ConfigArray["newsxmlfilename"];

    $this->Rep->setLimit($this->XMLNewCount);

    $NewsList = $this->Rep->getAll();

    $file = new TRMFile();

    $file->openFile($filename, "w+");
    
    $this->startNewsToXML($file);
    $k = 0;
    foreach( $NewsList as $row )
    {
        $this->addNewsToXML($file, $row["news"]);
        $k++;
        if($k == 30 ) { break; }
    }
    $this->stopNewsToXML($file);
    
    $file->addBufferTo();
    $file->closeFile();
}

private function startNewsToXML($file)
{
    $file->addToBuffer('<?xml version="1.0" encoding="'.GlobalConfig::$ConfigArray["Charset"].'" ?>'.PHP_EOL);
    $file->addToBuffer('<rss version="2.0">'.PHP_EOL);
    $file->addToBuffer('<channel>'.PHP_EOL);
    $file->addToBuffer('<title>'.GlobalConfig::$ConfigArray["SiteName"].'</title>'.PHP_EOL);
    $file->addToBuffer('<link>'.GlobalConfig::$ConfigArray["CommonURL"].'</link>'.PHP_EOL);
    $file->addToBuffer('<description>'.GlobalConfig::$ConfigArray["NewsTitle"].'</description>'.PHP_EOL);
    $file->addToBuffer('<language>ru</language>'.PHP_EOL);
}

private function stopNewsToXML($file)
{
    $file->addToBuffer('</channel>'.PHP_EOL);
    $file->addToBuffer('</rss>'.PHP_EOL);
}

private function prepareString($str)
{
    $newstr = str_replace("<br>", "<br />", $str);
    $newstr = str_replace("\"", "&quot;", $newstr);
    $newstr = str_replace("\'", "&apos;", $newstr);
    $newstr = str_replace("&", "&amp;", $newstr);
    $newstr = str_replace(">", "&gt;", $newstr);
    $newstr = str_replace("<", "&lt;", $newstr);
    return $newstr;
}

private function addNewsToXML($file, $row)
{
    $ID_new = $row["ID_new"];
    $link = $row["link"];
    $description = $this->prepareString($row["description"]);
    $author = $this->prepareString($row["author"]);
    $category = $this->prepareString($row["category"]);
    $comments = $this->prepareString($row["comments"]);
    $enclosure = $row["enclosure"];
    $guid = $row["guid"];
    $pubDate = $row["pubDate"];
    $source = $row["source"];
    $chanelid = $row["chanelid"];

    $file->addToBuffer('<item>'. PHP_EOL);
    $file->addToBuffer('<title>'.$category.'</title>'.PHP_EOL);
    $file->addToBuffer('<link>'.$link.'</link>'.PHP_EOL);
    $file->addToBuffer('<description>'.$description.'</description>'.PHP_EOL);
    $file->addToBuffer('<author>'.$author.'</author>'.PHP_EOL);
    $file->addToBuffer('<pubDate>'.$pubDate.'</pubDate>'.PHP_EOL);
    $file->addToBuffer('</item>'.PHP_EOL);
}


} // NewAjaxNewsController
