<?php

namespace NewCMS\Domain;

use NewCMS\DataObjects\NewIdTranslitDataObject;
use NewCMS\Domain\Exceptions\NewArticlesExceptions;

/**
 * класс производителя товаров
 */
class NewArticle extends NewIdTranslitDataObject
{
/**
 * @var array - имя свойства для идентификатора объекта, обычно совпадает с именем ID-поля из БД
 */
static protected $IdFieldName = array( "articles", "ID_article" );
/**
 * @var array - имя свойства с названием или заголовком объекта, обычно совпадает с полем name или title из таблицы БД
 */
static protected $TitleFieldName = array( "articles", "Title" );
/**
 * @var array - имя свойства с транскрипцией названия на английском - используется для URL товара, группы или другого документа
 */
static protected $TranslitFieldName = array( "articles", "ArticleURL" );


/**
 * @return string - возвращает имя тима текущего документа-статьи
 * @throws NewArticlesExceptions
 */
public function getArticleTypeName()
{
    if( !count($this->DataArray) )
    {
        throw new NewArticlesExceptions( "Данные документа отсутсвуют!" );
    }
    return $this->getData("articlestype", "ArticlesTypeName");
}


} // NewArticle
