<?php

namespace NewCMS\Yandex;

use TRMEngine\DataObject\TRMIdDataObject;

/**
 * информацмя о товаре для прайса на Яндекс.Маркет
 *
 * @date 2019-10-26
 */
class NewYandexMarketProduct extends TRMIdDataObject
{
/**
 * @var array - имя свойства для идентификатора объекта, обычно совпадает с именем ID-поля из БД
 */
static protected $IdFieldName = array( "new_yandex_market_products", "ID_Price" );


} // NewYandexMarketProduct
