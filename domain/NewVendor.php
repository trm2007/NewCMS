<?php

namespace NewCMS\Domain;

use NewCMS\DataObjects\NewIdTranslitDataObject;

/**
 * класс производителя товаров
 */
class NewVendor extends NewIdTranslitDataObject
{
/**
 * @var array - имя свойства для идентификатора объекта, обычно совпадает с именем ID-поля из БД
 */
static protected $IdFieldName = array( "vendors", "ID_vendor" );
/**
 * @var array - имя свойства с названием или заголовком объекта, обычно совпадает с полем name или title из таблицы БД
 */
static protected $TitleFieldName = array( "vendors", "VendorName" );
/**
 * @var array - имя свойства с транскрипцией названия на английском - используется для URL товара, группы или другого документа
 */
static protected $TranslitFieldName = array( "vendors", "VendorTranslit" );
/**
 * @var NewVendor - экземляр родительского объекта тоже типа NewVendor, если есть
 */
private $ParentVendorObject = null;


/**
 * @return NewVendor - возвращает объект родительской группы
 */
public function getParentVendorObject()
{
    return $this->ParentVendorObject;
}

/**
 * @param NewGroup $ParentVendorObject - устанавливает объект родительской группы
 */
public function setParentVendorObject(NewVendor $ParentVendorObject)
{
    $this->ParentVendorObject = $ParentVendorObject;
}

public function jsonSerialize()
{
    $Arr = parent::jsonSerialize();
    if( $this->ParentVendorObject ) { $Arr["Parent"] = $this->ParentVendorObject; }
    
    return $Arr;
}


} // NewVendor
