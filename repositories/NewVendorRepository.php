<?php

namespace NewCMS\Repositories;

use NewCMS\Domain\NewVendor;
use TRMEngine\DataMapper\TRMDataMapper;
use TRMEngine\DataObject\Interfaces\TRMDataObjectInterface;
use TRMEngine\DataSource\TRMSqlDataSource;
use TRMEngine\Exceptions\TRMSqlQueryException;


class NewVendorRepository extends NewRepository
{
static protected $DataObjectMap = array(
    "vendors" => array(
        TRMDataMapper::STATE_INDEX => TRMDataMapper::FULL_ACCESS_FIELD,
        TRMDataMapper::FIELDS_INDEX => array(
            "ID_vendor" => array(
                TRMDataMapper::KEY_INDEX => "PRI",
            ),
        ),
    ),
);
/**
 * @var boolean - флаг, 
 * указывающий на необходимость собирать всю информацию родителей
 */
protected $FullParentInfoFlag = false;


public function __construct(TRMSqlDataSource $DataSource)
{
    parent::__construct(NewVendor::class, $DataSource);
}

/**
 * задает сортировку коллекции при выборке из БД по дополнительному полю в дополнение к стандвртному набору
 * 
 * @param string $FieldName - имя поля по которому нужно сортировать спислк групп, 
 * если передано одно поле GroupOrder, то порядок его сортировки изменится на новое значение
 * @param boolean $AscFlag - если true - по возрастанию, 0 - по убыванию
 * @param int $FieldQuoteFlag - флаг, указывающий на необходимость заключать сортируемые поля в кавычки
 * 
 * @return void
 */
public function setOrderBy($FieldName = "", $AscFlag = true, $FieldQuoteFlag = TRMSqlDataSource::NEED_QUOTE )
{

    if( $FieldName == "VendorOrder" )
    {
        $this->DataSource->setOrderField( $FieldName, $AscFlag );
        return;
    }
    // очистка значений сортировки
    $this->DataSource->clearOrder();

    if( !empty($FieldName) )
    {
        $this->DataSource->setOrderField( $FieldName, $AscFlag, $FieldQuoteFlag );
    }
    $this->DataSource->setOrderField( "VendorOrder" );
}

/**
 * @return boolean - флаг, 
 * указывающий на необходимость собирать всю информацию родителей
 */
public function getFullParentInfoFlag()
{
    return $this->FullParentInfoFlag;
}
/**
 * @param boolean $FullParentInfoFlag - флаг, 
 * указывающий на необходимость собирать всю информацию родителей
 */
public function setFullParentInfoFlag($FullParentInfoFlag = true)
{
    $this->FullParentInfoFlag = $FullParentInfoFlag;
}

/**
 * 
 * @param array $DataArray
 * @param TRMDataObjectInterface $DataObject
 * @return NewVendor
 */
protected function getDataObjectFromDataArray(array &$DataArray, TRMDataObjectInterface $DataObject = null)
{
    $NewDataObject = parent::getDataObjectFromDataArray($DataArray, $DataObject);
    
    // если собирать информацию из родительских групп не надо, 
    // то возвращаем объект
    if( !$this->FullParentInfoFlag )
    {
        return $NewDataObject;
    }
    // если есть родитель, то получаем его из БД
    if( $NewDataObject["vendors"]["VendorID_parent"] )
    {
        // в родительском методе TRMIdDataObjectRepository::getById
        // выполнится проверка на наличе объекта с таким Id в контейнере репозитория
        // если объекта с ID не надется, то последует новый запрос к DataSource,
        $NewDataObject->setParentVendorObject( 
                $this->getById( $NewDataObject["vendors"]["VendorID_parent"] ) 
            );
    }
    return $NewDataObject;
}

public function getCountOfVendors()
{
    $Query = "SELECT count(`ID_vendor`) FROM `vendors`";

    $Res = $this->DataSource->getDBObject()->query($Query);
    if( !$Res )
    {
        throw new TRMSqlQueryException( "Ошибка в таблице производителей!" );
    }
    return $Res->fetch_row()[0];
}


} // NewVendorRepository

