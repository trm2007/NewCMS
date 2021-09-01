<?php

namespace NewCMS\Domain;

use NewCMS\DataObjects\NewIdTranslitDataObject;

/**
 * класс производителя товаров
 */
class NewFeature extends NewIdTranslitDataObject
{
/**
 * @var array - имя свойства для идентификатора объекта, обычно совпадает с именем ID-поля из БД
 */
static protected $IdFieldName = array( "features", "ID_Feature" );
/**
 * @var array - имя свойства с названием или заголовком объекта, обычно совпадает с полем name или title из таблицы БД
 */
static protected $TitleFieldName = array( "features", "FeatureTitle" );
/**
 * @var array - имя свойства с транскрипцией названия на английском - используется для URL товара, группы или другого документа
 */
static protected $TranslitFieldName = array( "features", "FeaturesTranslit" );


} // NewFeature
