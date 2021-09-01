<?php

namespace NewCMS\Libs\Sender;

use NewCMS\Libs\Sender\Exceptions\NewEmailAutoSenderException;
use TRMEngine\EMail\Exceptions\TRMEMailExceptions;
use TRMEngine\EMail\TRMEMail;
use TRMEngine\File\TRMFile;
use TRMEngine\File\TRMStringsFile;
use TRMEngine\Helpers\TRMState;

/**
 * рассылка сообщения из файла в HTML-формате 
 * по адресам из текстового файла,
 * поддерживается черный список
 */
class NewEmailAutoSender extends TRMState
{
/**
 * имя файла с сообщением по умолчанию
 */
const DEFAULT_MESSAGE_FILE_NAME = "1ps.txt";
/**
 * имя файла с информацией о последней сессии (по умолчанию)
 */
const DEFAULT_LAST_FILE_NAME = "last.txt";
/**
 * имя файла с базой E-mail адресов (по умолчанию)
 */
const DEFAULT_EMAIL_BASE_FILE_NAME = "base.txt";
/**
 * имя файла с адресами, по которым рассылка не производится (по умолчанию)
 */
const DEFAULT_BLACK_LIST_FILE_NAME = "blacklist.txt";
/**
 * имя файла с логом ошибочных адресов (по умолчанию)
 */
const DEFAULT_ERROR_LOG_FILE_NAME = "errorlog.txt";
/**
 * кол-во секунд ожидания перед следующей отправкой письма
 */
const SLEEP_SECONDS = 3;
/**
 * @var int  Кол-во отправляемых писем за 1 вызов скрипта
 */
protected $MessagesPerSession = 150;
/**
 * @var int  Дневной лимит на отправку
 */
protected $DayLimit = 2500;

/**
 * @var int - кол-во обработанных в предыдущие вызовы скрипта строк (адресов E-mail)
 */
protected $DoingLinesTotal = 0;
/**
 * @var int - кол-во уже отправленных писем (обработанных строк) за сегодня 
 */
protected $DoingLinesToday = 0;
/**
 * @var int - количество адресов из черного списка, 
 * которые встретились в сегодняшней рассылке
 */
protected $DoingBlackToday = 0;
/**
 * @var int  - общее количество адресов из черного списка, 
 * которые встретились в текущей рассылке
 */
protected $DoingBlackTotal = 0;

/**
 * @var date - дата, записанная во время последнего выполнения
 */
protected $LastDate = 0;
/**
 * @var string - послений обработанный адрес в рамках текущей рассылки
 */
protected $LastAddress = "";
/**
 * @var strung - последний успешно отправленный адрес 
 */
protected $LastSuccessAddress = "";
/**
 * @var array - массив с E-mail-адресами
 */
protected $EmailAddresses = array();
/**
 * @var array - массив - черный список с E-mail-адресов, по ним рассылка не производится
 */
protected $BlackListAddresses = array();
/**
 * @var string  Тема письма
 */
protected $SubjectOriginal = "";
/**
 * @var string  Текст письма
 */
protected $MessageOriginal = "";

/**
 * @var string - текущий рабочий каталог,
 * по умолчанию текущий каталог скрипта
 */
protected $CurrentWorkDir = __DIR__;
/**
 * @var string  имя файла относительно текущего рабочего каталога (корня сайта) $CurrentWorkDir
 */
protected $MessageFileName = __DIR__ . "/" . self::DEFAULT_MESSAGE_FILE_NAME;
/**
 * @var string - имя файла с информацией о количестве выполненых отправлений
 */
protected $LastFileName = __DIR__ . "/" . self::DEFAULT_LAST_FILE_NAME;
/**
 * @var string - имя файла с адресами для рассылки, 
 * каждый адрес должен быть на новой строке
 */
protected $EmailBaseFileName = __DIR__ . "/" . self::DEFAULT_EMAIL_BASE_FILE_NAME;
/**
 * @var string - имя файла с черным списком, 
 * по которому рассылку делать не нужно, каждый адрес должен быть на новой строке
 */
protected $BlackListFileName = __DIR__ . "/" . self::DEFAULT_BLACK_LIST_FILE_NAME;
/**
 * @var string - имя файда, в котором будут сохранятся адреса, по которым не удалось отправить
 */
protected $ErrorLogFileName = __DIR__ . "/" . self::DEFAULT_ERROR_LOG_FILE_NAME;

/**
 * @var string - Картинка, вставляемая в письмо
 */
protected $ImgFileName = "logo1.gif";
/**
 * @var array - массив с настройками Email для отправки
 */
protected $EmailConfig = array();
/**
 * @var string - адрес от имени котрого производится рассылка
 */
protected $ServiceEmail = "";
/**
 * @var string - имя компании, от которой производится рассылка
 */
protected $CompanyName = "";
/**
 * @var string - имя сайта, от имени которого производится рассылка
 */
protected $SiteName = "";


public function __construct(array $EmailConfig)
{
    $this->CurrentWorkDir = __DIR__;
    
    $this->SiteName = filter_input(INPUT_SERVER, "SERVER_NAME", FILTER_SANITIZE_ENCODED); // $_SERVER["SERVER_NAME"];
    $this->resetLastInfo();
    $this->setEmailConfig($EmailConfig);

    // Тема письма
    $this->SubjectOriginal = "Новости";
    if( !empty($this->CompanyName) )
    {
        $this->SubjectOriginal .= " " . $this->CompanyName;
    }
    if( !empty($this->SiteName) )
    {
        $this->SubjectOriginal .= " - " . $this->SiteName;
    }
}

/**
 * 
 * @param array $EmailConfig - массив с настройками TRMEmail для отправки почты,
 * если содержит ключи "smtpnamefrom" или "nameto", то имя компании CompanyName 
 * будет установлено в одно из этиъ значений (сначала проверяется "smtpnamefrom")
 */
public function setEmailConfig(array $EmailConfig)
{
    $this->EmailConfig = $EmailConfig;
    // Адрес отправителя
    if( isset($EmailConfig["smtpemailfrom"]) )
    {
        $this->ServiceEmail = $EmailConfig["smtpemailfrom"];
    }
    elseif( isset($EmailConfig["emailfrom"]) )
    {
        $this->ServiceEmail = $EmailConfig["emailfrom"];
    }

    if( isset($EmailConfig["smtpnamefrom"]) )
    {
        $this->CompanyName = $EmailConfig["smtpnamefrom"];
    }
    elseif( isset($EmailConfig["namefrom"]) )
    {
        $this->CompanyName = $EmailConfig["namefrom"];
    }
}

/**
 * получает текст сообщения из файла $this->MessageFileName
 * 
 * @throws NewEmailAutoSenderException
 */
public function getMessageFromFile()
{
    $MessageFile = new TRMFile($this->MessageFileName);
    if( !$MessageFile->getAllFileToBuffer() )
    {
        throw new NewEmailAutoSenderException($MessageFile->getStateString());
    }
    $this->MessageOriginal = $MessageFile->getBuffer();
}
/**
 * сохраняет текст сообщения в файл $FilePath, 
 * имя файла берется из $this->MessageFileName
 * 
 * @throws NewEmailAutoSenderException
 */
public function saveMessageToFile()
{
    $MessageFile = new TRMFile($this->MessageFileName);
    $MessageFile->setBuffer($this->MessageOriginal);
    if( !$MessageFile->putBufferTo() )
    {
        throw new NewEmailAutoSenderException($MessageFile->getStateString());
    }
}

/**
 * обнуляет все данные о последней рассылке
 */
protected function resetLastInfo()
{
    // кол-во обработанных в предыдущие вызовы скрипта строк (адресов E-mail)
    $this->DoingLinesTotal = 0;
    // кол-во уже отправленных писем (обработанных строк) за сегодня 
    $this->DoingLinesToday = 0;
    // дата, записанная во время последнего выполнения
    $this->LastDate = time();
    // адрес последней отправки
    $this->LastAddress = "";
    // количество адресов из черного списка за сегодня
    $this->DoingBlackToday = 0;
    // количество адресов из черного списка за всю текущую рассылку
    $this->DoingBlackTotal = 0;
    $this->LastSuccessAddress = "";
}

/**
 * получает последнее состяние рассылки из файла "last.txt" : 
 * сколько отпралвено всего, сколько отпралвено за сегодня и дату последнего обновления
 * 
 * @return boolean - если файл с Last Info не обнаружен, значит рассылка не запущена, вернет false,
 * в случае успшного получения всех данных вернет true
 * 
 * @throws NewEmailAutoSenderException
 */
public function getLastFromFile()
{
    $LastFile = new TRMStringsFile($this->LastFileName);
    
    if( !$LastFile->existFile() )
    {
        $this->resetLastInfo();
        return false;
    }
    else if( !$LastFile->getEveryStringToArrayFrom($this->LastFileName , FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) )
    {
        throw new NewEmailAutoSenderException($LastFile->getStateString());
    }
    $StringsArray = $LastFile->getArrayBuffer();
    // кол-во обработанных в предыдущие вызовы скрипта строк (адресов E-mail)
    $this->DoingLinesTotal = intval( $StringsArray[0] );
    // кол-во уже отправленных писем (обработанных строк) за сегодня 
    $this->DoingLinesToday = intval( $StringsArray[1] );
    // дата, записанная во время последнего выполнения
    $this->LastDate = intval( $StringsArray[2] );
    // адрес последней отправки
    $this->LastAddress = $StringsArray[3];
    
    if( isset($StringsArray[4]) )
    {
        // количество адресов из черного списка за всю текущую рассылку
        $this->DoingBlackTotal = $StringsArray[4];
    }
    else
    {
        $this->DoingBlackTotal = 0;
    }
    if( isset($StringsArray[5]) )
    {
        // количество адресов из черного списка за сегодня
        $this->DoingBlackToday = $StringsArray[5];
    }
    else
    {
        $this->DoingBlackToday = 0;
    }
    if( isset($StringsArray[6]) )
    {
        $this->LastSuccessAddress = $StringsArray[6];
    }
    else
    {
        $this->LastSuccessAddress = "";
    }

    return true;
}
/**
 * обнуляет всю информацию в файле с информацией о последней рассылке 
 * только если загружен список адресов 
 * и текущая рассылка завершена 
 * 
 * @return boolean - если удалось перезапустить, вернет true, иначе false
 * 
 * @throws NewEmailAutoSenderException
 */
public function resetLastFile()
{
    // если файл с информацией о последней рассылке (Last Info) не обнаружен,
    if( !$this->getLastFromFile() )
    {
        // тогда getLastFromFile() обнулит все данные и 
        // saveLastToFile создаст файл с нулевыми значениями
        $this->saveLastToFile();
        return true;
    }
    // если информация в Last-файле есть, 
    // но рассылка не закончена, то перезапустить ее не получится
    if( !$this->isTotalFinished() )
    {
        return false;
    }
    // если файл есть и текущая рассылка завершена, 
    // тогда обнуляем все счетчики 
    // и записываем нулевые значения в файл Last Info
    $this->resetLastInfo();
    $this->saveLastToFile();
    return true;
}

/**
 * записывает информацию о текущем состоянии рассылки (Last Info) в файл
 * 
 * @throws NewEmailAutoSenderException
 */
public function saveLastToFile()
{
    $LastFile = new TRMStringsFile($this->LastFileName);
    //$LastFile->openFile("", "w+");
    $LastFile->addStringToArray( $this->DoingLinesTotal );
    $LastFile->addStringToArray( $this->DoingLinesToday );
    
    $this->LastDate = time();
    $LastFile->addStringToArray( $this->LastDate );
    $LastFile->addStringToArray( $this->LastAddress );
    $LastFile->addStringToArray( $this->DoingBlackTotal );
    $LastFile->addStringToArray( $this->DoingBlackToday );
    $LastFile->addStringToArray( $this->LastSuccessAddress );
    
    if( !$LastFile->putStringsArrayTo() )
    {
        throw new NewEmailAutoSenderException($LastFile->getStateString());
    }
}
/**
 * принудительное удаление файла с информацией о рассылке (Last Info), 
 * это действие остановит текущую рассылку, если она еще не закончена
 */
public function deleteLastFile()
{
    if(file_exists($this->LastFileName) )
    {
        unlink($this->LastFileName);
    }
    $this->resetLastInfo();
}

/**
 * получает массив строк с E-mail-адресами из файла $FilePath
 * 
 * @param string $FilePath - полный путь к файлу, если не задан, 
 * то берется текущая директория скрипта и файл по умолчанию "base.txt"
 */
public function getEmailBaseFromFile($FilePath = "", $BlackFilePath = "")
{
    if(empty($FilePath)) { $FilePath = $this->EmailBaseFileName; }
    else { $this->EmailBaseFileName = $FilePath; }
    $BaseFile = new TRMStringsFile();
    //  FILE_IGNORE_NEW_LINES - Пропускать новую строку в конце каждого элемента массива 
    // FILE_SKIP_EMPTY_LINES - Пропускать пустые строки 
    if( !$BaseFile->getEveryStringToArrayFrom($FilePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) )
    {
        throw new NewEmailAutoSenderException($BaseFile->getStateString());
    }
    // Массив адресов для рассылки
    $this->EmailAddresses = $BaseFile->getArrayBuffer();

    if(empty($BlackFilePath)) { $BlackFilePath = $this->BlackListFileName; }
    else { $this->BlackListFileName = $BlackFilePath; }

    //  FILE_IGNORE_NEW_LINES - Пропускать новую строку в конце каждого элемента массива 
    // FILE_SKIP_EMPTY_LINES - Пропускать пустые строки 
    if( !$BaseFile->getEveryStringToArrayFrom($BlackFilePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) )
    {
        $this->BlackListAddresses = array();
    }
    else
    {
        $this->BlackListAddresses = $BaseFile->getArrayBuffer();
    }
    // наличие адресов в черном списке проверяется по ходу рассылки
    // исходный массив адресов не меняется
//    if( !empty($this->BlackListAddresses) )
//    {
//        $this->EmailAddresses = array_diff($this->EmailAddresses, $this->BlackListAddresses);
//    }
}
/**
 * Записывает соержимое массива с базой E-mail адресов в файл $this->EmailBaseFileName,
 * в случае неудачной записи выбрасывает исключение
 * 
 * @throws NewEmailAutoSenderException
 */
public function saveEmailAddressesToFile()
{
    $BaseFile = new TRMStringsFile($this->EmailBaseFileName);
    $BaseFile->setArrayBuffer($this->EmailAddresses);
    if( $BaseFile->putStringsArrayTo() === false )
    {
        throw new NewEmailAutoSenderException(
                "Запись базы E-mail адресов прошла неудачно!" 
                . PHP_EOL 
                . $BaseFile->getStateString(),
            503);
    }
}

/**
 * Записывает соержимое массива с базой E-mail адресов в файл $this->EmailBaseFileName,
 * в случае неудачной записи выбрасывает исключение
 * 
 * @throws NewEmailAutoSenderException
 */
public function saveBlackListAddressesToFile()
{
    $BaseFile = new TRMStringsFile($this->BlackListFileName);
    $BaseFile->setArrayBuffer($this->BlackListAddresses);
    if( $BaseFile->putStringsArrayTo() === false )
    {
        throw new NewEmailAutoSenderException(
                "Запись списка исключений E-mail адресов прошла неудачно!" 
                . PHP_EOL 
                . $BaseFile->getStateString(),
            503);
    }
}

/**
 * @return int - возвращает количество адресов в загруженной базе
 */
public function getTotalCount()
{
    return count($this->EmailAddresses);
}

/**
 * сохраняет Log-файл с адресами, по которым не удвлось отправить письма
 * 
 * @param array $ErrorAddresses - массив строк-адресов
 * @throws NewEmailAutoSenderException
 */
protected function addToErrorLogFile(array $ErrorAddresses)
{
    // табуляция
    $Separator = chr(9);
    $Now = date("Y-m-d [H:i:s]");
    $FilePath = $this->ErrorLogFileName;
    $ErrorLogFile = new TRMStringsFile($FilePath);
    
    foreach ($ErrorAddresses as $EmailArddress)
    {
        $ErrorLogFile->addStringToArray($Now . $Separator . $EmailArddress);
    }
    if( false === $ErrorLogFile->putStringsArrayTo("a") )
    {
        throw new NewEmailAutoSenderException($ErrorLogFile->getStateString());
    }
}

/**
 * @return boolean - если выполнены все условия для завершения рассылки сегодня, 
 * то возвращает true, иначе false
 */
public function isTodayFinished()
{
    // Дневной лимит достигнут или превышен
    if($this->DoingLinesToday >= $this->DayLimit)
    {
        $this->addStateString("Дневной лимит достигнут ({$this->DoingLinesToday})");
        return true;
    }
    return false;
}

/**
 * @return boolean - Если выполнены все условия для завершения рассылки, 
 * т.е. письма отправлены по всем адресам из базы, 
 * то возвращает true, иначе false
 */
public function isTotalFinished()
{
    // Если отправлено писем столько же или больше, чем есть в базе адресов
    if( $this->DoingLinesTotal >= count($this->EmailAddresses) )
    {
        $this->addStateString("Рассылка завершена ({$this->DoingLinesTotal})");
        return true;
    }
    return false;
}

/**
 * подготовлка к отправке, проверка настроек
 * 
 * @param boolean $StartIfLastFileEmptyFlag - если установлен в TRUE, 
 * то при отсутвии информации о послежней рассылке, начинает новую (по умолчанию)
 * 
 * @return boolean
 */
protected function prepareSending($StartIfLastFileEmptyFlag = true)
{
    // если в базе нет адресов, завершаем
    if( empty($this->EmailAddresses) )
    {
        $this->getEmailBaseFromFile();
        if( empty($this->EmailAddresses) )
        {
            $this->setStateString("в базе нет адресов");
            return false;
        }
    }
    // текст письма
    if( empty($this->MessageOriginal) )
    {
        $this->getMessageFromFile();
        if( empty($this->MessageOriginal) )
        {
            $this->setStateString("пустое сообщение");
            return false;
        }
    }
    // информация о последней рассылке
    if( !$this->getLastFromFile() )
    {
        // если файл не обнаружен, но стоит флаг перезагрузки
        if($StartIfLastFileEmptyFlag)
        {
            // обнуляет всю информацию о рассылке в объекте
            $this->resetLastInfo();
            // записывает новый файл с начальной информацией о рассылке
            $this->resetLastFile();
        }
        else
        {
            $this->setStateString("не найден файл с информацией о рассылке {$this->LastFileName}, "
                                . "для старта новой рассылки сначала нужно вызвать resetLastFile");
            return false;
        }
    }
    
    // если достигнут дневной предел, проверяет начало нового дня
    if( $this->isTodayFinished() )
    {
        // Если текущая дата не равна последней сохраненной, 
        // значит это новый день, 
        // обнуляем счетчик обработанных строк (адресов рассылки) за день
        if(date("d", time()) != date("d", $this->LastDate) )
        {
            $this->DoingLinesToday = 0;
        }
        else
        {
            return false;
        }
    }

    // если все уже отправлено, то завершаем
    if( $this->isTotalFinished() )
    {
        return false;
    }
    // проверка, что бы адрес последней рассылки находился на том же месте,
    // иначе база нарушена и рассылка может попасть повторно тем же адресатам,
    // а этого делпть нельзя!
    if( $this->DoingLinesTotal>0 && trim($this->EmailAddresses[$this->DoingLinesTotal-1]) != trim($this->LastAddress) )
    {
        $this->setStateString("нарушена целостность базы адресов, "
                . "текущая рассылка не может быть продолжена, "
                . "необходимо восстановить исходную базу для текущей рассылки, "
                . "или изменить файлы конфигурации вручную. "
                . "последний адрес отправки: {$this->LastAddress} "
                . "адрес для отправки из базы: {$this->EmailAddresses[$this->DoingLinesTotal-1]} "
                . "всего отправлено: {$this->DoingLinesTotal} ");
        return false;
    }
    
    return true;
}

/**
 * рассылка писем
 */
public function runSending()
{
    if( !$this->prepareSending() )
    {
        throw new NewEmailAutoSenderException($this->getStateString());
    }
    // кол-во успещно отправленных сообщений
    $SuccessNum = 0;
    // кол-во не отправленных сообщений
    $ErrorNum = 0;
    // массив с адресами не отправленных сообщений
    $ErrorEmail = array();
    // Создаем объект для работы с почтой TRMEmail
    $email = new TRMEMail();
    $email->setConfigArray( $this->EmailConfig );
    if( !empty($this->ServiceEmail) )
    {
        $email->setEmailFrom($this->ServiceEmail);
        $email->setReplyTo( $this->ServiceEmail, $this->CompanyName );
    }
    if( !empty($this->CompanyName) )
    {
        $email->setNameFrom($this->CompanyName);
    }
    $email->setSubject($this->SubjectOriginal);
    $email->setMessage($this->MessageOriginal);
    $email->addInlineImages($this->ImgFileName);

    // общее число строк (адресов) для рассылки
    $TotalCount = count( $this->EmailAddresses );
    // отсчет строк (адресов) в рамках текущей сесии
    // $CountLines - кол-во обработанных в текущей сессии строк (адресов)
    for( 
        $CountLines=0; 
        $CountLines<$this->MessagesPerSession && $this->DoingLinesToday<$this->DayLimit && $this->DoingLinesTotal<$TotalCount; 
        $CountLines++, $this->DoingLinesToday++, $this->DoingLinesTotal++
    )
    {
        $this->LastAddress = $this->EmailAddresses[$this->DoingLinesTotal];
        if( in_array($this->LastAddress, $this->BlackListAddresses) )
        {
            $this->DoingBlackToday++;
            $this->DoingBlackTotal++;
            continue;
        }
        $email->setEmailTo($this->LastAddress);
        try
        {
            $email->sendEmail();
            sleep(self::SLEEP_SECONDS);
            $SuccessNum++;
            $this->LastSuccessAddress = $this->LastAddress;
        }
        catch(TRMEMailExceptions $e)
        {
            $ErrorEmail[] = $e->getFile() . ":" . $e->getLine() . " \"" . $e->getMessage() . " \" - " . $this->LastAddress;
            $ErrorNum++;
        }
    } // конец цикла for прохода по строкам-адресам

    // Если общее кол-во отправленных писем равно общему кол-ву строк, 
    // значит больше адресов нет, 
    // записываем в файл максимально возможное число
    if( $this->DoingLinesTotal == $TotalCount )
    {
        $this->DoingLinesTotal = PHP_INT_MAX;
    }
    // сохраняем информацию о  текущей сессии
    $this->saveLastToFile();
    // записываем в файл адреса с ошибкой отправления
    $this->addToErrorLogFile($ErrorEmail);
    echo "Успешно - " . $SuccessNum . PHP_EOL;
    echo "Неудачно - " . $ErrorNum . PHP_EOL;
}


public function setBlackListFileName(array $BlackListFileName)
{
    $this->BlackListFileName = $BlackListFileName;
}
/**
 * @param array $EmailAddresses - устанавливает массив с адресами для рассылки,
 * массив не сортируется!
 */
public function setEmailAddresses(array $EmailAddresses)
{
//    if( sort($EmailAddresses) )
//    {
        $this->EmailAddresses = $EmailAddresses;
//    }
}
public function setBlackListAddresses(array $EmailAddresses)
{
    $this->BlackListAddresses = $EmailAddresses;
}

public function setSubjectOriginal($SubjectOriginal)
{
    $this->SubjectOriginal = $SubjectOriginal;
}

public function setMessageOriginal($MessageOriginal)
{
    $this->MessageOriginal = $MessageOriginal;
}

public function setMessageFileName($MessageFileName)
{
    $this->MessageFileName = $MessageFileName;
}

public function setLastFileName($LastFileName)
{
    $this->LastFileName = $LastFileName;
}

public function setEmailBaseFileName($EmailBaseFileName)
{
    $this->EmailBaseFileName = $EmailBaseFileName;
}

public function setImgFileName($ImgFileName)
{
    $this->ImgFileName = $ImgFileName;
}

public function setCurrentWorkDir($CurrentWorkDir)
{
    $this->CurrentWorkDir = $CurrentWorkDir;
}
/**
 * @param int $MessagesPerSession - кол-во отправляемых сообщений за сессию
 */
public function setMessagesPerSession($MessagesPerSession)
{
    $this->MessagesPerSession = $MessagesPerSession;
}
/**
 * @param int $DayLimit - лимит на кол-во писем, рассылаемых за день
 */
public function setDayLimit($DayLimit)
{
    $this->DayLimit = $DayLimit;
}

/**
 * @return array - имя файла, в котором хранится база с адресами, 
 * по которым рассылка запрещена - черный список
 */
public function getBlackListFileName()
{
    return $this->BlackListFileName;
}

/**
 * @return array - массив с адресами, по которым рассылка запрещена - черный список
 */
public function getBlackEmailAddresses()
{
    return $this->BlackListAddresses;
}
/**
 * @return array - массив с адресами для рассылки
 */
public function getEmailAddresses()
{
    return $this->EmailAddresses;
}
/**
 * @return string - тема письма
 */
public function getSubjectOriginal()
{
    return $this->SubjectOriginal;
}
/**
 * @return string - сообщение для рассылки
 */
public function getMessageOriginal()
{
    return $this->MessageOriginal;
}

/**
 * @return int - общее количество обработанных (разосланных) E-mail адресов
 */
public function getDoingLinesTotal()
{
    return $this->DoingLinesTotal;
}
/**
 * @return int - кол-во обработаных (разосланных) E-mail адресов за сегодня
 */
public function getDoingLinesToday()
{
    return $this->DoingLinesToday;
}
/**
 * @return date - дата последнего обновления
 */
public function getLastDate()
{
    return $this->LastDate;
}
/**
 * @return string - последний обработанный адрес в рассылке
 */
public function getLastAddress()
{
    return $this->LastAddress;
}
/**
 * @return string - адрес последенй успешной отправки
 */
public function getLastSuccessAddress()
{
    return $this->LastSuccessAddress;
}
/**
 * @return string - полный путь к файлу, содержащему информацию о последней рассылке
 */
public function getLastFileName()
{
    return $this->LastFileName;
}
/**
 * @return string - полный путь к файлу, содержащему информацию о последней рассылке
 */
public function getEmailBaseFileName()
{
    return $this->EmailBaseFileName;
}

/**
 * @return int - кол-во адресов из черного списка, 
 * встретившихся в сегодняшней рассылке
 */
public function getDoingBlackToday()
{
    return $this->DoingBlackToday;
}

/**
 * @return int - кол-во адресов из черного списка, 
 * встретившихся всего в текущей рассылке
 */
public function getDoingBlackTotal()
{
    return $this->DoingBlackTotal;
}

/**
 * @param int $Number - номер записи в базе адресов
 * 
 * @return string - E-mai адрес под данным номером
 */
public function getEmailAdsress($Number)
{
    return isset($this->EmailAddresses[$Number]) ? $this->EmailAddresses[$Number] : null;
}


} // NewEmailAutoSender