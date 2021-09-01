<?php

namespace NewCMS\Domain;

use TRMEngine\DataObject\TRMDataObjectsContainer;

/**
 * класс для работы с продуктом из таблицы table1 без вспомогательных объектов
 * 2018-06-30
 *
 * @author TRM
 */
class NewProduct extends TRMDataObjectsContainer
{
/**
 * @var NewLiteProduct - основной объект
 */
protected $MainDataObject;
/**
 * @var string - тип главного объекта 
 */
static protected $MainDataObjectType = NewLiteProduct::class;

public function __construct()
{
    $this->MainDataObject = new NewLiteProduct();

    // добавляем зависимости
    // setDependence("имя объекта внутри контейнера", "тип объекта", "объект(таблица) и поле в MainDataObject, по которому устанавливается связь с Id")
    $this->setDependence("VendorObject", NewVendor::class, "table1", "vendor");
    $this->setDependence("GroupObject", NewGroup::class, "table1", "Group");
}

public function getVendorObject()
{
    return $this->getDependenceObject( NewVendor::class );
}

public function getGroupObject()
{
    return $this->getDependenceObject( NewGroup::class );
}

/**
 * устанавливает базовую (начальную) цену товара и вычисляет 3 цены с наценками
 * 
 * @param double $Price0 - начальная цена в валюте товара
 */
public function setPrice0($Price0)
{
     $this->MainDataObject->setPrice0($Price0);
}


} // NewProduct