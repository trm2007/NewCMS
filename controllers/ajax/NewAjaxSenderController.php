<?php

namespace NewCMS\Controllers\AJAX;

use NewCMS\Libs\Sender\Exceptions\NewEmailAutoSenderException;
use NewCMS\Libs\Sender\NewEmailAutoSender;
use Symfony\Component\HttpFoundation\Request;
use TRMEngine\DiContainer\TRMDIContainer;

/**
 * обработка AJAX-запросов для настройки EmailAutoSender-a
 */
class NewAjaxSenderController extends NewAjaxCommonController
{
/**
 * @var string - путь к файлу с текстом сообщения
 */
protected $MessageFileName = "";
/**
 * @var NewEmailAutoSender
 */
protected $Sender;


public function __construct(Request $Request, TRMDIContainer $DIC)
{
    parent::__construct($Request, $DIC);
    
    $this->Sender = new NewEmailAutoSender( require_once CONFIG . "/emailconfig.php" );
    $this->MessageFileName = getcwd() . "/web/libs/1ps.txt";
}

/**
 * возвращает объект файла сообщения (строку) в виде JSON
 */
public function actionGetMessage()
{
    $this->Sender->getMessageFromFile();
    
    echo json_encode($this->Sender->getMessageOriginal());
}

/**
 * сохраняет файл сообщения
 */
public function actionSetMessage()
{
    $json = file_get_contents('php://input');
    $this->Sender->setMessageOriginal(json_decode($json, true));
    $this->Sender->saveMessageToFile();
    
    echo json_encode($this->Sender->getMessageOriginal());
}

/**
 * возвращает JSON-объект c базой e-mail адресов
 */
public function actionGetBase()
{
    $this->Sender->getEmailBaseFromFile();
    
    echo json_encode($this->Sender->getEmailAddresses());
}

/**
 * сохраняет полученную базу e-mail адресов в файл
 */
public function actionSetBase()
{
    $json = file_get_contents('php://input');

    $NewArr = json_decode($json, true);
    if(empty($NewArr))
    {
        throw new NewEmailAutoSenderException("Нет данных для добавления!");
    }
    $this->Sender->getEmailBaseFromFile();
    // добавляем так, 
    // что бы новые элементы попадали всегда в конец, 
    // что бы не нарушать последовательность рассылки
    $OldArr = $this->Sender->getEmailAddresses();
    // получаем разницу между существующим и вновь присланным массивом с адресами
    $DiffArr = array_diff($NewArr, $OldArr);
    // формируем новый массив как объединение разницы со старыми данными
    $NewArr = array_merge($OldArr, $DiffArr);
    // если нет информации о последней рассылке, 
    // или общее количество разосланых писем равно или больше количеству адресов в базе,
    // значит рассылка завершена
    if( !$this->Sender->getLastFromFile() || $this->Sender->getDoingLinesTotal() >= $this->Sender->getTotalCount() )
    {
        // если рассылка не запущена, то перед добавлением сортируем массив
        sort($NewArr);
    }
    $this->Sender->setEmailAddresses($NewArr);
    $this->Sender->saveEmailAddressesToFile();

    echo json_encode($this->Sender->getEmailAddresses());
}

/**
 * возвращает JSON-объект c базой e-mail адресов
 */
public function actionGetBlackBase()
{
    $this->Sender->getEmailBaseFromFile();
    
    echo json_encode($this->Sender->getBlackEmailAddresses());
}

/**
 * сохраняет полученную базу e-mail адресов в файл
 */
public function actionSetBlackBase()
{
    $json = file_get_contents('php://input');

    $NewArr = json_decode($json, true);
    if(empty($NewArr))
    {
        throw new NewEmailAutoSenderException("Нет данных для добавления!");
    }
    // getEmailBaseFromFile - получает и базу E-mail 
    // и черный список из фала, если есть
    $this->Sender->getEmailBaseFromFile();
    // добавляем так, 
    // что бы новые элементы попадали всегда в конец, 
    // что бы не нарушать последовательность рассылки
    $OldArr = $this->Sender->getBlackEmailAddresses();
    // получаем разницу между существующим и вновь присланным массивом с адресами
    $DiffArr = array_diff($NewArr, $OldArr);
    // формируем новый массив как объединение разницы со старыми данными
    $NewArr = array_merge($OldArr, $DiffArr);
    // для черного списка не привязки к рассылке,
    // перед добавлением сортируем массив
    sort($NewArr);

    $this->Sender->setBlackListAddresses($NewArr);
    $this->Sender->saveBlackListAddressesToFile();

    echo json_encode($this->Sender->getBlackEmailAddresses());
}

/**
 * вспомогательная функция, формирует массив с Last Info
 * 
 * @return array - возвращает сгенерированный массив с данными о последней рассылке
 */
private function generateLastInfoArray()
{
    $this->Sender->getLastFromFile();
    $Arr = array();
    $Arr["TotalCount"] = $this->Sender->getTotalCount();
    $Arr["DoingTotal"] = $this->Sender->getDoingLinesTotal();
    $Arr["DoingToday"] = $this->Sender->getDoingLinesToday();
    $Arr["DoingBlackTotal"] = $this->Sender->getDoingBlackTotal();
    $Arr["DoingBlackToday"] = $this->Sender->getDoingBlackToday();
    $Arr["LastDate"] = date("Y-m-d H:i:s", $this->Sender->getLastDate() );
    $Arr["LastAddress"] = $this->Sender->getLastAddress();
    // отсчет начинается с 0, поэтому последний отправленный должен находиться 
    // в базе на месте (кол-во отправленных) - 1
    $Arr["LastTestAddress"] = $this->Sender->getEmailAdsress($Arr["DoingTotal"]-1);
    $Arr["LastSuccessAddress"] = $this->Sender->getLastSuccessAddress();
    
    return $Arr;
}

/**
 * запускает рассылку путем удаления файла с информацией о послденей рассылке,
 * должен быть настроен Cron
 * 
 * @throws NewEmailAutoSenderException
 */
public function actionReStartSending()
{
    $this->Sender->getEmailBaseFromFile();

    if( !$this->Sender->resetLastFile() )
    {
        $Arr["Msg"] = "Текущая рассылка не завершена. Запустить новую нельзя!";
    }
    else
    {
        $Arr["Msg"] = "Рассылка запущена сначала!";
    }
    
    echo json_encode( array_merge( $Arr, $this->generateLastInfoArray($this->Sender) ), JSON_FORCE_OBJECT );
}

/**
 * отпралвяет клиенту информацию о посленей рассылке,
 * TotalCount - общее кол-во адресов, 
 * DoingTotal - общее кол-во отправленных, 
 * DoingToday - кол-во отпралвенных за сегодня, 
 * LastDate - и дата послденего изменения
 */
public function actionGetLastInfo()
{
    $this->Sender->getEmailBaseFromFile();

    // возвращает массив в виде JSON-объекта
    echo json_encode( $this->generateLastInfoArray($this->Sender), JSON_FORCE_OBJECT );
}

/**
 * принудительная остановка рассылки, 
 * удаление файла с информацией о рассылке
 */
public function actionStopSending()
{
    $this->Sender->deleteLastFile();
    $Arr["Msg"] = "Информация удалена. Рассылка остановлена!";
    $this->Sender->getEmailBaseFromFile();
    echo json_encode( array_merge( $Arr, $this->generateLastInfoArray($this->Sender) ), JSON_FORCE_OBJECT );
}


} // NewAjaxSenderController
