<?php

namespace NewCMS\Controllers\AJAX;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use TRMEngine\Image\TRMImage;

/**
 * Загрузка изображений и файлов от клиента на сервер
 *
 * @author TRM
 */
class AjaxImageServiceController // extends \TRMEngine\TRMController
{
/** добавить дату */
const ADD_DATE = 128;
/** добавить количество секунд соласно UNIX с 01/01/1970 */
const ADD_TIMESTAMP = 256;
/** добавить случайные цифры */
const ADD_RANDOM_DIGITS = 512;
/** добавить случайную строку */
const ADD_RANDOM_STRING = 1024;
/**
 * @var array - список обрабатываемых MIME-типов файлов 
 */
protected $ImageFormat= array( "image/jpeg", "image/jpg", "image/gif", "image/png" ); //, "image/tif" );
/**
 * @var int - максимальный размер файла
 */
protected $MaxSize = 4194304; // 4*1024*1024;
/**
 * @var int - минимальная высота изображения
 */
protected $MinH = 200;
/**
 * @var int - минимальная ширина изображения
 */
protected $MinW = 200;
/**
 * @var string - сообщение об ошибке
 */
protected $Message = "";
/**
 * @var Request - объект запроса от клиента
 */
protected $Request;

/**
 * @var UploadedFile - объект загруженного фала
 */
protected $UploadFile;

/**
 * @param Request $Request
 */
public function __construct(Request $Request)
{
    $this->Request = $Request;
}

/**
 * срабатывает при загрузке картинки через CKEditor v < 5.0
 */
public function actionUploadImage()
{
    $RandomPostFixFlag = $this->Request->request->getBoolean("RandomPostFixFlag", false);

    $FullPathArray = $this->uploadFile(\GlobalConfig::$ConfigArray["UploadImageFolder"], $RandomPostFixFlag);
    if( empty($FullPathArray) )
    {
        $this->exitOnLoadError();
    }
    $FullPath = $FullPathArray[0] . '/' . $FullPathArray[1] . '.' . $FullPathArray[2];
    $callback = $this->Request->query->get("CKEditorFuncNum");
    echo '<script type="text/javascript">window.parent.CKEDITOR.tools.callFunction('
        . '"'.$callback.'",'
        . ' "'.$FullPath.'",'
        . ' "'.$this->Message.'" '
    . ');</script>';
}

/**
 * срабатывает при загрузке картинки через CKEditor5
 */
public function actionCkEditor5UploadImage()
{
    $FullPathArray = $this->uploadFile(\GlobalConfig::$ConfigArray["UploadImageFolder"], true);
    if( empty($FullPathArray) )
    {
        $this->exitOnLoadError();
    }

    if(defined("DEBUG") )
    {
        header('Access-Control-Allow-Origin: *');
    }
    
    $Sheme = \TRMEngine\Helpers\TRMLib::getServerProtcol();

    $FullName = $Sheme // $this->Request->server->get("REQUEST_SCHEME")
            . "://"
            . $this->Request->server->get("HTTP_HOST") 
            . '/' . $FullPathArray[0] 
            . '/' . $FullPathArray[1] 
            . '.' . $FullPathArray[2];
    $ResArr = array(
        "default" => $FullName
    );
    header("Content-type: application/json; charset=utf-8");
    echo json_encode($ResArr);
}

/**
 * из переданного файла формирует две картинки JPEG и уменьшенная GIF 
 * и сохраняет их на сервере
 */
public function actionUploadCatalogImage()
{
    $this->MinW = \GlobalConfig::$ConfigArray["ProductPreviewMaxW"];
    $this->MinH = \GlobalConfig::$ConfigArray["ProductPreviewMaxH"];
    
    $FullPathArray = $this->uploadCatalogImage();
    if( empty($FullPathArray) )
    {
        $this->exitOnLoadError();
    }
    if(defined("DEBUG") )
    {
        header('Access-Control-Allow-Origin: *');
    }

    header("Content-type: application/json; charset=utf-8");
    
    echo json_encode($FullPathArray);
}

/**
 * из переданного файла формирует две картинки JPEG исходного размера и уменьшенного,
 * сохраняет на сервере
 */
public function actionUploadGroupImage()
{
    $this->MinW = \GlobalConfig::$ConfigArray["GroupPreviewMaxW"];
    $this->MinH = \GlobalConfig::$ConfigArray["GroupPreviewMaxH"];

    $FullPathArray = $this->uploadGroupImage();
    if( empty($FullPathArray) )
    {
        $this->exitOnLoadError();
    }
    if(defined("DEBUG") )
    {
        header('Access-Control-Allow-Origin: *');
    }

    header("Content-type: application/json; charset=utf-8");
    
    echo json_encode($FullPathArray);
}

/**
 * срабатывает при загрузке файла,
 * в аргументах POST-запроса проверяется Catalog - имя каталога, 
 * в который нужно поместить закачанный файл,
 * и RandomPostFixFlag - флаг, 
 * указывающий нужно ли добавлять случайную добавку к имени фала из 9 случайных цифр
 */
public function actionUploadFile()
{
    $DefaultCharset = "utf-8";

    $CatalogName = $this->Request->request->get("Catalog", "");
    $RandomPostFixFlag = $this->Request->request->getBoolean("RandomPostFixFlag", false);

    $FullPathArray = $this->uploadFile($CatalogName, $RandomPostFixFlag);
    if( empty($FullPathArray) )
    {
        $this->exitOnLoadError();
    }

    // Если включен DEBUG то принимаем запросы от всех клиентов, 
    // а не только от скриптов этого же домена,
    // нужен был для отладки скриптов AJAX (axios) с локального компьютера
    if(defined("DEBUG") )
    {
        header('Access-Control-Allow-Origin: *');
    }

    header("Content-type: application/json; charset=utf-8");
    
    // для отладки...
    if(strtolower(\GlobalConfig::$ConfigArray["Charset"]) !== $DefaultCharset )
    {
        $FullPathArray[] = \TRMLib::conv($this->Message, \GlobalConfig::$ConfigArray["Charset"], $DefaultCharset) ;
    }
    else
    {
        $FullPathArray[] = $this->Message;
    }
    
    echo json_encode($FullPathArray);
}

/**
 * сохранеят, переданный в форме через POST, файл на сервере
 * 
 * @param string $CatalogName - каталог на сервере, в который будет закачан файл,
 * без имени домена!!!
 * @param boolean $RandomPostFixFlag - если устанлвлен в TRUE, 
 * то к имени файла будут добавлены 9 случайных цифр
 * 
 * @return array - [$CatalogName, $NameWithoutExt, $Ext]
 */
protected function uploadFile( $CatalogName, $RandomPostFixFlag = false )
{
/**
 * объект загруженного фала
 * @var UploadedFile $this->UploadFile
 */
    $this->UploadFile = $this->Request->files->get('upload');
    
    if( !$this->validateUploadFile( $this->UploadFile ) )
    {
        return array();
    }

    $Ext = $this->UploadFile->getClientOriginalExtension(); // $ExtArray['extension'];
//    $Ext = $this->UploadFile->guessExtension();

    $NameWithoutExt = str_replace("." . $Ext, "", $this->UploadFile->getClientOriginalName());
    if( $RandomPostFixFlag )
    {
        $NameWithoutExt .= '-' . self::generateAddon(); // sprintf( "%'.09d", rand(0,999999999) );
    }
    $NameWithExt = $NameWithoutExt . '.' . $Ext;

    // папка в которую загружается файл на сервере, без закрывающего /
    $UploadDir = rtrim($this->Request->server->get("DOCUMENT_ROOT"), "/\\");
    if( !empty($CatalogName) )
    {
        $UploadDir .= "/" . trim($CatalogName, "/\\");
    }

    move_uploaded_file($this->UploadFile->getPathname(), $UploadDir . "/" . $NameWithExt);

    return array($CatalogName, $NameWithoutExt, $Ext);
}

/**
 * Генерирует строчку, обычно используемую для уникализации имени файла,
 * могут быть сгенерированы различные значения в зависимости от параметров
 * 
 * @param int $Param - одна из констант: AjaxImageServiceController::ADD_DATE - 
 * генерирует дату в формате ГГГГ-ММ-ДД, AjaxImageServiceController::ADD_TIMESTAMP - 
 * генерирует текущий UNIX Timestamp, AjaxImageServiceController::ADD_RANDOM - 
 * генерирует случайные есколько цифр, количество указывается в $DigitsCount
 * @param int $DigitsCount - если $Param = AjaxImageServiceController::ADD_RANDOM, 
 * то $DigitsCount содержит количество цифр, которые нужно сгенерировать, количество от 1 до 9,
 * если указать $DigitsCount больше 9, то будут сгенерированы только 9 цифр.
 * Если $Param = AjaxImageServiceController::ADD_RANDOM_STRING, 
 * то $DigitsCount содержит количество символов, которые нужно сгенерировать в строке, от 1 до 32.<br>
 * Если $DigitsCount будет меньше единицы, то добавится одна цифра.
 * 
 * @return string - сгенерированная строка
 */
static public function generateAddon($Param = self::ADD_RANDOM_DIGITS, $DigitsCount = 9)
{
    $Addon = "";
    switch($Param)
    {
        case self::ADD_DATE: 
            $Addon .= date("Y-m-d");
            break;
        case self::ADD_TIMESTAMP: 
            $Addon .= (string)time();
            break;
        case self::ADD_RANDOM_DIGITS: 
            if($DigitsCount > 9) { $DigitsCount = 9; }
            if($DigitsCount < 1) { $DigitsCount = 1; }
            $Addon .= sprintf( "%'.0{$DigitsCount}d", rand(0, pow(10, $DigitsCount)-1 ) );
            break;
        case self::ADD_RANDOM_STRING: 
            if($DigitsCount > 32) { $DigitsCount = 32; }
            if($DigitsCount < 1) { $DigitsCount = 1; }
            $Addon .= substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyz'), 0, $DigitsCount);
            break;
    }
    
    return $Addon;
}

/**
 * сохраняет, переданный в форме через POST, файл на сервере 
 * в двух форматах GIF-маленький и JPEG-исходный,
 * к имени файла в конец добавляются случайные 9 цифр
 * 
 * @return array - array($CatalogName, $NameWithoutExt)
 */
protected function uploadCatalogImage()
{
/**
 * объект загруженного фала
 * @var UploadedFile $this->UploadFile
 */
    $this->UploadFile = $this->Request->files->get('upload');
    
    if( !$this->validateUploadFile( $this->UploadFile ) )
    {
        return array();
    }

    $Ext = $this->UploadFile->getClientOriginalExtension(); // $ExtArray['extension'];

    $NameWithoutExt = str_replace("." . $Ext, "", $this->UploadFile->getClientOriginalName());
    $NameWithoutExt .= '-' . sprintf( "%'.09d", rand(0,999999999) );

    $CatalogName = trim(\GlobalConfig::$ConfigArray["ImageCatalog"], "\\/"); 
    // папка в которую загружается файл на сервере, без закрывающего /
    if( !$this->generateGIFAndJPGImage(
            $NameWithoutExt, 
            ROOT . "/" . $CatalogName, 
            $this->UploadFile->getPathname() ) 
        )
    {
        $this->exitOnLoadError();
    }
    unlink($this->UploadFile->getPathname());

    return array($CatalogName, $NameWithoutExt);
}

/**
 * сохраняет, переданный в форме через POST, 
 * файл на сервере в двух размерах в папке для картинок групп
 * (в данной версии в \GlobalConfig::$ConfigArray["GroupImagesCatalog"])
 * к имени файла в конец добавляются случайные 9 цифр
 * 
 * @return array - array($CatalogName, $NameWithoutExt, $SmallPrefix . $NameWithoutExt, $Ext)
 */
protected function uploadGroupImage()
{
    $SmallPrefix = "small-";
/**
 * объект загруженного фала
 * @var UploadedFile $this->UploadFile
 */
    $this->UploadFile = $this->Request->files->get('upload');
    
    if( !$this->validateUploadFile( $this->UploadFile ) )
    {
        return array();
    }

    $Ext = $this->UploadFile->getClientOriginalExtension(); // $ExtArray['extension'];

    $NameWithoutExt = str_replace("." . $Ext, "", $this->UploadFile->getClientOriginalName());
    $NameWithoutExt .= '-' . sprintf( "%'.09d", rand(0,999999999) );

    $CatalogName = trim(\GlobalConfig::$ConfigArray["GroupImagesCatalog"], "\\/"); 

    // папка в которую загружается файл на сервере, без закрывающего /
    if( !$this->generateSmallAndNormalImage(
            $NameWithoutExt, 
            ROOT . "/" . $CatalogName, 
            $SmallPrefix,
            $this->UploadFile->getPathname(),
            $Ext, 
            $Ext ) 
        )
    {
        $this->exitOnLoadError();
    }
    unlink($this->UploadFile->getPathname());

    return array($CatalogName, $NameWithoutExt, $SmallPrefix . $NameWithoutExt, $Ext);
}

/**
 * формирует изображение из $SourceImageFileFullPath, 
 * сохраняет его в виде GIF размером $this->MinW, $this->MinH
 * и в обычном размере в формате JPEG
 * 
 * @param string $ImageFileName - только имя файла, без пути и расширения
 * @param string $ImageFilePath - папка с изображениями на сервере, должна быть доступна из WEB, без закрывающего /
 * @param string $SourceImageFileFullPath - полное имя файла исходного изображения, которое было закачано
 * 
 * @return boolean
 */
private function generateGIFAndJPGImage($ImageFileName, $ImageFilePath, $SourceImageFileFullPath)
{
    return $this->generateSmallAndNormalImage(
            $ImageFileName, 
            $ImageFilePath, 
            "", 
            $SourceImageFileFullPath, 
            "jpg", 
            "gif");

    if( file_exists($ImageFilePath . "/" . $ImageFileName . ".jpg") && file_exists($ImageFilePath . "/" . $ImageFileName . ".gif") )
    {
        return true;
    }

    $GIFImage = new TRMImage();
    $JPGImage = new TRMImage();

    if( !$GIFImage->getImageFromFile($SourceImageFileFullPath) || !$JPGImage->getImageFromFile($SourceImageFileFullPath) )
    {
        $this->Message .= "Картинку из {$SourceImageFileFullPath} получить не удалось!";
        return false;
    }

    if(!$GIFImage->generateNewSizeImage( $this->MinW, $this->MinH, IMAGETYPE_GIF ))
    {
        $this->Message .= "Ошибка формирования нового GIF-изображения!!!";
        return false;
    }
    // $JPGImage->generateNewSizeImage() без аргументов формирует новое изображение 
    // с исходыми размерами типа IMAGETYPE_JPEG
    if(!$JPGImage->generateNewSizeImage())
    {
        $this->Message .= "Ошибка формирования нового JPG-изображения!!!";
        return false;
    }

    // сохраняет картинку в формате IMAGETYPE_GIF (как указано в аргументе для generateNewSizeImage)
    $GIFImage->DestCatalog = $ImageFilePath;
    $GIFImage->saveDestImageToFile( $ImageFileName );
    // сохраняет картинку в формате IMAGETYPE_JPEG (как указано в аргументе для generateNewSizeImage)
    $JPGImage->DestCatalog = $ImageFilePath;
    $JPGImage->saveDestImageToFile( $ImageFileName );
    
    $this->Message .= $ImageFilePath . "/" . $ImageFileName . PHP_EOL;

    return true;
}

/**
 * определяет тип изображения по расширению
 * 
 * @param string $Ext - расширение файла для получения PHP-типа изображения
 * 
 * @return int - одна из констант PHP с типом изображения IMAGETYPE_xxx
 */
private function getTypeImageFromExtention($Ext)
{
    switch($Ext)
    {
        case "gif": return IMAGETYPE_GIF;
        case "png": return IMAGETYPE_PNG;
    }
    // по умолчанию всегда JPEG
    return IMAGETYPE_JPEG;
}

/**
 * сохраняет изображение из $SourceImageFileFullPath в 2-х размерах - исходном 
 * и уменьшенном - $this->MinW Х $this->MinH 
 * в форматах указанных для этих двух изображений $NormalType и $SmallType
 * 
 * @param string $ImageFileName - только имя файла, без пути и расширения
 * @param string $ImageFilePath - папка с изображениями на сервере, должна быть доступна из WEB, без закрывающего /
 * @param string $SmallPrefix - префикс для имени изображения с уменьшенным размером
 * @param string $SourceImageFileFullPath - полное имя файла исходного изображения, которое было закачано
 * @param string $NormalType - тип сохраняемой картинки с исходным размером - строка "jpeg", "gif" или "png"
 * @param string $SmallType - тип сохраняемой картинки с уменшенным размером - строка "jpeg", "gif" или "png"
 * 
 * @return boolean
 */
private function generateSmallAndNormalImage($ImageFileName, $ImageFilePath, $SmallPrefix, $SourceImageFileFullPath, $NormalType, $SmallType)
{
    $NormalImageExt = strtolower($NormalType);
    $SmallImageExt = strtolower($SmallType);
    
    $NormalImageType = $this->getTypeImageFromExtention($NormalImageExt);
    $SmallImageType = $this->getTypeImageFromExtention($SmallImageExt);
    
    if( file_exists($ImageFilePath . "/" . $ImageFileName . "." . $NormalImageExt) 
        && file_exists($ImageFilePath . "/" . $SmallPrefix . $ImageFileName . "." . $SmallImageExt) )
    {
        return true;
    }

    // объект для уменьшенного изображения
    $SmallImage = new TRMImage();
    // объект для изображения с исходными размерами
    $NormalImage = new TRMImage();

    if( !$SmallImage->getImageFromFile($SourceImageFileFullPath) 
        || !$NormalImage->getImageFromFile($SourceImageFileFullPath) )
    {
        $this->Message .= "Картинку из {$SourceImageFileFullPath} получить не удалось!";
        return false;
    }

    if(!$SmallImage->generateNewSizeImage( $this->MinW, $this->MinH, $SmallImageType ))
    {
        $this->Message .= "Ошибка формирования нового {$SmallImageExt}-изображения!!!";
        return false;
    }
    // $NormalImage->generateNewSizeImage() с нулевыми аргуменами размеров формирует новое изображение 
    // с исходыми размерами типа $NormalImageType
    if(!$NormalImage->generateNewSizeImage(0, 0, $NormalImageType))
    {
        $this->Message .= "Ошибка формирования нового {$NormalImageExt}-изображения!!!";
        return false;
    }

    // сохраняет картинку в формате $SmallImageType (как указано в аргументе для generateNewSizeImage)
    $SmallImage->DestCatalog = $ImageFilePath;
    $SmallImage->saveDestImageToFile( $SmallPrefix . $ImageFileName );
    // сохраняет картинку в формате $NormalImageType (как указано в аргументе для generateNewSizeImage)
    $NormalImage->DestCatalog = $ImageFilePath;
    $NormalImage->saveDestImageToFile( $ImageFileName );
    
    $this->Message .= $ImageFilePath . "/" . $ImageFileName . PHP_EOL;

    return true;
}

/**
 * валидация полученного через _POST файла
 * @param UploadedFile $UploadFile
 * @return boolean
 */
private function validateUploadFile( UploadedFile $UploadFile )
{
    if( !$this->UploadFile->isValid() )
    {
        $this->Message .= "Ошибка загрузки файла. Возможно, Вы не выбрали файл";
        return false;
    }
    if( $this->UploadFile->getSize() == 0 )
    {
        $this->Message .= "Передан файл нулевого размера";
        return false;
    }
    if( $this->UploadFile->getSize() > $this->MaxSize )
    {
        $this->Message .= "Максимальный размер загружаемого файла {$this->MaxSize} байт";
        return false;
    }
    if( !in_array( $this->UploadFile->getMimeType(), $this->ImageFormat ) )
    {
        $ImageTypeStr = implode(", ", $this->ImageFormateces);
        $this->Message .= "Допускается загрузка следующих форматов: {$ImageTypeStr}";
        return false;
    }

    return true;
}

/**
 * вызывается если произошла ошибка при загрузке файла
 */
private function exitOnLoadError()
{
    http_response_code(500);
    header("HTTP/1.0 500 Internal Server Error ");
    echo json_encode("Ошибка загрузки файла! " . $this->Message );
    exit;
}


} // AjaxImageServiceController