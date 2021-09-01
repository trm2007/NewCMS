<?php

namespace NewCMS\Domain;

use NewCMS\DataObjects\NewIdTranslitDataObject;
use NewCMS\Libs\NewHelper;

/**
 * класс для работы с продуктом из таблицы table1 без вспомогательных объектов
 * 2018-07-28
 *
 * @author TRM
 */
class NewLiteProduct extends NewIdTranslitDataObject
{
/**
 * @var array - имя свойства для идентификатора объекта, обычно совпадает с именем ID-поля из БД
 */
static protected $IdFieldName = array( "table1", "ID_price" );
/**
 * @var array - имя свойства с названием или заголовком объекта, обычно совпадает с полем name или title из таблицы БД
 */
static protected $TitleFieldName = array( "table1", "Name" );

/**
 * @var array - имя свойства с транскрипцией названия на английском - используется для URL товара, группы или другого документа
 */
static protected $TranslitFieldName = array( "table1", "PriceTranslit" );


/**
 * устанавливает для объекта значение ID-поля первичного ключа!!!
 * для этого первичный ключ должен быт задан в getIdFieldName()
 * так же устанавливает это значение для поля ID_goods
 *
 * @param mixed $id - ID-объекта
 */
public function setId($id)
{
    parent::setId($id);
    $this->setData("goodsdescription", "ID_goods", $id);
}

/**
 * обнуляет ID-объекта и поля ID_goods
 * эквивалентен setId(null);
 */
public function resetId()
{
    parent::resetId();
    $this->setData("goodsdescription", "ID_goods", null);
}

/**
 * устанавливает базовую (начальную) цену товара и вычисляет 3 цены с наценками
 * 
 * @param double $Price0 - начальная цена в валюте товара
 */
public function setPrice0($Price0)
{
    $this->setData("table1", "price0", $Price0);
    $this->setData("table1", "PriceRUB", TRMValuta::convert( $Price0, $this->getData("table1", "valuta") ));
    NewHelper::setPrices( $this, $this->getData("table1", "PriceRUB") );
}

/**
 * 
 * @return array - массив array(Price1, Price2, Price3), индекс начинается с 0
 */
public function getPriceArray()
{
    return array(
        $this->getData("table1", "Price1"),
        $this->getData("table1", "Price2"),
        $this->getData("table1", "Price3")
    );
}

/**
 * проверяет критические данные товара (имя, родительская группа),
 * если они не установлены, то выбрасывается исключение
 * 
 * @throws NewProductsExceptions
 */
public function validate()
{
    if(!$this->getData("table1", "Group"))
    {
        throw new NewProductsExceptions( "Не установлена родительская группа!", 503);
    }
    if(!$this->getData("table1", "Name"))
    {
        throw new NewProductsExceptions( "Не задано название товара!", 503);
    }
    if(!$this->getData("table1", "PriceTranslit"))
    {
        $this->translit();
    }
}


} // NewLiteProduct