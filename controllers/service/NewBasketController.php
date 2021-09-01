<?php

namespace NewCMS\Controllers;

use NewCMS\Domain\NewComplexOrder;
use NewCMS\Libs\NewBasket;
use NewCMS\Views\ArticlesBaseView;
use NewCMS\Views\CMSBaseView;
use Symfony\Component\HttpFoundation\Request;
use TRMEngine\DiContainer\TRMDIContainer;
use TRMEngine\EMail\Exceptions\TRMEMailExceptions;
use TRMEngine\EMail\TRMEMail;
use TRMEngine\Helpers\TRMLib;

/**
 * контроллер для работы с корзиной товаров
 */
class NewBasketController extends NewController
{
/**
 * кодировка по упмолчанию, 
 * а так же в этой кодировке приходят данные из формы через POST от клиентов
 */
const DEFAULT_CHARSET = "utf-8";
/**
 * @var Basket - экземпляр объекта корзины
 */
protected $CurrentBasket;
    /**
 * @type string - сохраняется сообщения при обработке и попытке отправить заказ из корзины
 */
private $StatusText;

const EmptyBasketText = "Корзина пуста";

function __construct(Request $Request, TRMDIContainer $DIC)
{
    parent::__construct($Request, $DIC);
    $this->StatusText = '';
    
    if( strtolower($this->Request->attributes->get("action")) !== "index" )
    {
        $this->CurrentBasket = new NewBasket($this->_RM);
    }
}

/**
 * вызывается при простом обращении к /basket
 * отображает начальную страницу работы с корзиной
 */
public function actionIndex()
{
    // ********* форма с товарами !!! ****************
    $this->view = new ArticlesBaseView($this, "basket");

    $this->view->setTitle(\GlobalConfig::$ConfigArray["BasketTitle"]);
    $this->view->setKeyWords(\GlobalConfig::$ConfigArray["CommonKeyWords"] . ", заказ товаров");
    $this->view->setDescription(\GlobalConfig::$ConfigArray["CompanyTitle"] . " - ".\GlobalConfig::$ConfigArray["BasketTitle"]);
    $this->view->setVar("PageTitle", \GlobalConfig::$ConfigArray["BasketTitle"]);
    $this->view->setVarsArray(\GlobalConfig::$ConfigArray);
    $this->view->addCSS( TOPIC . "/css/basket.css", true);
    $this->view->addCSS( TOPIC . "/css/forcatalogpage.css", true);

    return $this->view->render();
}

/**
 * выводит имеющиеся товары в корзине в форму
 */
public function actionForm()
{
    if($this->CurrentBasket->getGoodsFromCookies())
    {
        $this->CurrentBasket->initGoodsFromDB();

        $this->view = new CMSBaseView("basketform", null );
        $this->view->setVar("Goods", $this->CurrentBasket->Goods );
        $this->view->setVar("catalogPrefix", \GlobalConfig::$ConfigArray["catalogPrefix"]);
        $this->view->setVar("ImageCatalog", \GlobalConfig::$ConfigArray["ImageCatalog"]);
        return $this->view->render();
    }    
    
    echo self::EmptyBasketText;
    exit;
}

/**
 * вычисление и вывод общей стоимости товаров в корзине
 * 
 * @return boolean
 */
public function actionGetCost()
{
    if($this->CurrentBasket->getGoodsFromCookies())
    {
        $this->CurrentBasket->initGoodsFromDB();
        $Summ = $this->CurrentBasket->calculateSumm();
        echo $Summ;
    }
    exit;
}

/**
 * вызывается при подтверждении отправки формы с корзиной товаров
 * отправляет заказ по eMail
 */
public function actionConfirm()
{
    $Message = "";
    $this->StatusText = "";

    if( !$this->CurrentBasket->getGoodsFromCookies() )
    {
        $this->StatusText = self::EmptyBasketText;
    }  
    try
    {
        $this->CurrentBasket->initGoodsFromDB();

        for($i=0; $i < count($this->CurrentBasket->Goods); $i++)
        {
            $Message .= ($i+1)
                    . " - [{$this->CurrentBasket->Goods[$i]->Item->getId()}] "
                    . "<a href=\"" 
                            . ( ( strlen($this->Request->server->get("HTTPS")) && $this->Request->server->get("HTTPS", null) != "off") ? "https://" : "http://")
                            . $this->Request->getHost() . "/" 
                            . \GlobalConfig::$ConfigArray["catalogPrefix"] . "/" 
                            . $this->CurrentBasket->Goods[$i]->Item->getData("table1", "PriceTranslit") . "\">"
                    . $this->CurrentBasket->Goods[$i]->Item->getData("table1", "Name")
                    . "(".$this->CurrentBasket->Goods[$i]->Item->getData("vendors", "VendorName").")</a> - "
                    . $this->CurrentBasket->Goods[$i]->Count." ".$this->CurrentBasket->Goods[$i]->Item->getData("unit", "UnitShort")."<br>";
        }

        if( isset(\GlobalConfig::$ConfigArray["PriceCheck"]) )
        {
            $Message .= "На сумму <b>" . $this->CurrentBasket->calculateSumm() . "</b> руб.<br>";
            header("X-PriceCheck: 1");
        }

        $emailaddress = $this->Request->request->get("email");
        $fio = $this->Request->request->get("fio");
        $msg = $this->Request->request->get("message");
        $phone = $this->Request->request->get("phone");

        if( empty($emailaddress) )
        {
            throw new TRMEMailExceptions("Передан пустой E-mail адрес!");
        }
        if( empty($fio) )
        {
            $fio = $emailaddress;
        }

        // Все, что приходит из формы, приходит в кодировке DEFAULT_CHARSET = UTF-8,
        // перекодируем в Charset установленный для проекта...
        if( strtolower(\GlobalConfig::$ConfigArray["Charset"]) !== self::DEFAULT_CHARSET )
        {
            TRMLib::conv($msg, self::DEFAULT_CHARSET, \GlobalConfig::$ConfigArray["Charset"]);
            TRMLib::conv($fio, self::DEFAULT_CHARSET, \GlobalConfig::$ConfigArray["Charset"]);
        }

        $email = new TRMEMail();


        $email->setEmailFrom($emailaddress);
        $email->setNameFrom($fio);

        $email->setConfig( CONFIG . "/emailconfig.php" );
        $email->setReplyTo( $emailaddress, $fio );

        $email->setMessage($Message);
        $email->addMessage("-----------------------------<br>");
        $email->addMessage($msg);
        $email->addMessage("<br>\nTel: ");
        $email->addMessage($phone);
        $email->addMessage("<br>\nE-mail: ");
        $email->addMessage($emailaddress);
        $email->addMessage("<br>\nName: ");
        $email->addMessage($fio);

        if( $email->sendEmail() ) 
        {
            $this->StatusText .= "<h3>Заказ успешно отправлен!</h3>"
                    . "Через некоторое время вам будет отправлен счет для оплаты на указанный E-mail,"
                    . " если возникнут вопросы, с вами свяжутся для уточнения заказа.";

            // если отправлено сообщение, записываем заказ в БД
            $OrderRep = $this->_RM->getRepository(NewComplexOrder::class);
            $ComplexOrder = $OrderRep->getNewObject();
//                $ComplexOrder = new NewComplexOrder();
            $ComplexOrder->setMessage($msg);
            $ComplexOrder->setFIO($fio);
            $ComplexOrder->setEmail($emailaddress);
            $ComplexOrder->setPhone($phone);
            $ComplexOrder->setDateFromTime( time() );
            $ComplexOrder->setSessionId(session_id());

            $ComplexOrder->initFromBasket($this->CurrentBasket);


            $OrderRep->insert($ComplexOrder);
            $OrderRep->doInsert();
        }
        else { $this->StatusText =  "Ошибка при отправлении заказа!!!"; }
    }
    catch (TRMEMailExceptions $e)
    {
        $this->StatusText = "Ошибка при отправлении заказа!!!<br>";
        //$this->StatusText .= "Возможно, адрес [" . $this->Request->request->get("youremail") . "] указан не верно!<br>";
        $this->StatusText .= " Исключение: " . $e->getMessage();
        //$this->StatusText = "Возможно, адрес [" . $emailaddress . "] указан не верно!";
    }

    
//    echo $this->StatusText."<br>".$Message; // TRMLib::conv($Message);
    echo $this->StatusText;
    exit;
}

/**
 * освобождаем корзину с товарами
 */
public function actionEmpty()
{
    $this->CurrentBasket->emptyBasket();
    echo self::EmptyBasketText;
    exit;
}


} // NewBasketController