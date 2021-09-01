<?php

namespace NewCMS\Domain;

use NewCMS\DataObjects\NewIdTranslitDataObject;

/**
 * класс производителя товаров
 */
class NewArticlesType extends NewIdTranslitDataObject
{
/**
 * @var array - имя свойства для идентификатора объекта, обычно совпадает с именем ID-поля из БД
 */
static protected $IdFieldName = array( "articlestype", "ID_articlestype" );
/**
 * @var array - имя свойства с названием или заголовком объекта, обычно совпадает с полем name или title из таблицы БД
 */
static protected $TitleFieldName = array( "articlestype", "ArticlesTypeName" );
/**
 * @var array - имя свойства с транскрипцией названия на английском - используется для URL товара, группы или другого документа
 */
static protected $TranslitFieldName = array( "articlestype", "ArticlesURL" );


} // NewArticlesType
