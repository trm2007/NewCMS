<?php

namespace NewCMS\Widgets;

use NewCMS\Repositories\NewLiteProductForCollectionRepository;
use NewCMS\Views\CMSBaseView;
use TRMEngine\DataObject\Interfaces\TRMDataObjectsCollectionInterface;
use TRMEngine\DataSource\TRMSqlDataSource;

/**
 * класс коллекции популярных товаров, 
 * собирает товары из стартовой группы, и из всех ее дочерних подгрупп
 */
class NewPrestigeProducts
{
/**
 * @var NewLiteProductForCollectionRepository репозиторий для уоллекции товаров
 */
protected $ProducstRepository;
/**
 * @var int - ID-группы, для которой выбираются популярные товары
 */
protected $GroupId;
/**
 * @var int - ID-производителя, для которого выбираются популярные товары
 */
protected $VendorId;


/**
 * Объект с коллекцией популярных товаров
 * 
 * @param int $StartGroupId - группа каталога, для которой выбираются популярные товары
 * @param NewLiteProductForCollectionRepository $Rep репозиторий товаров для коллекций
 * @param int $VendorId - ID-производителя, если передан, то выбираются только его товары
 */
public function __construct(NewLiteProductForCollectionRepository $Rep)
{
    $this->ProducstRepository = $Rep; // new NewLiteProductForCollectionRepository();
}

/**
 * @return int - ID группы, для которой выбираются популярные товары
 */
public function getGroupId()
{
    return $this->GroupId;
}
/**
 * @param int $GroupId - ID группы, для которой выбираются популярные товары
 */
public function setGroupId($GroupId)
{
    $this->GroupId = $GroupId;
}

/**
 * @return int - ID производителя, для которого собираются популярные товары
 */
public function getVendorId()
{
    return $this->VendorId;
}
/**
 * @param int $VendorId - устанавливает ID-производителя, выбираются только его товары
 */
public function setVendorId($VendorId)
{
    $this->VendorId = $VendorId;
}

/**
 * получает список популярных товаров в группе GroupId и ее дочерних,
 * если GroupId не была задана ранее через setGroupId, 
 * то будут получены товары для начальной группы из GlobalConfig
 * если был установлен VendorId через setVendorId,
 * то будут выбраться товары из группы только этого производителя,
 * иначе будут выбраны популярные товары всех производителей
 * 
 * @param int $Limit - максимальное количество товаров для выыборки, по умолчанию 10
 * @param int $PrestigeLimit - ограничение по рейтингу для товара, поумолчанию = 0 
 * (формируется на основании количества просмотров и начального рейтинга товара установленного в БД)
 * 
 * @return TRMDataObjectsCollectionInterface - возвращает коллекцию популярных товаров 
 * удовлетворяющих условиям поиска
 */
public function getPrestigeProducts($Limit=10, $PrestigeLimit=0)
{
    $this->ProducstRepository->clearCondition();
    if(!$this->GroupId)
    {
        $this->setGroupId( \GlobalConfig::$ConfigArray["StartGroup"] );
    }
    $this->ProducstRepository->setSubGroupsFlag();
    $this->ProducstRepository->setCurrentGroupId($this->GroupId);
    if($this->VendorId)
    {
        $this->ProducstRepository->removeCondition("table1", "vendor");
        $this->ProducstRepository->addCondition("table1", "vendor", $VendorId);
    }
    $this->ProducstRepository->setPresentFlagCondition();
    $this->ProducstRepository->addCondition( "table1", "price0", 0, ">");
    $this->ProducstRepository->setOrderBy("`table1`.`Visits`", false);

    $this->ProducstRepository->addCondition( "",
            "(`table1`.`Prestige`+`table1`.`Visits`)", 
            $PrestigeLimit, 
            ">", 
            "AND", 
            TRMSqlDataSource::NOQUOTE);
    $this->ProducstRepository->getDataSource()->setLimit($Limit);

    $ProductsList = $this->ProducstRepository->getAll();
    return $ProductsList;
}

/**
 * выводит в поток/клиенту HTML-представление полученных товаров
 * 
 * @param TRMDataObjectsCollectionInterface $PrestigeProductCollection
 */
static public function render(TRMDataObjectsCollectionInterface $PrestigeProductCollection)
{
    $ShortGoodsView = new CMSBaseView("onegoods", null);
    $ShortGoodsView->setPathToViews( ROOT . TOPIC . "/views/main/inc");

    $ShortGoodsView->setVar("showComment", false);

    foreach( $PrestigeProductCollection as $row1 )
    {
        $ShortGoodsView->setVarsArray( $row1["table1"] );
        $ShortGoodsView->setVarsArray( $row1["vendors"] );
        $ShortGoodsView->setVarsArray( $row1["unit"] );
        $ShortGoodsView->render();
    }
}


} // NewPrestigeProducts