<?php

namespace NewCMS\Domain;

use TRMEngine\DataObject\TRMIdDataObject;

/**
 * поисковые запросы
 */
class NewSearchQuery extends TRMIdDataObject
{
/**
 * @var array - имя объекта и свойства для идентификатора новости, 
 * обычно совпадают с именем таблицы и ID-поля из БД
 */
static protected $IdFieldName = array( "new_search_query", "ID_SearchQuery" );


public function __construct()
{
    $this->resetId();
    $this->setData( "new_search_query", "Text", "" );
    $this->setData( "new_search_query", "Time", date("Y-m-d H:i:s", time()) );
    $this->setData( "new_search_query", "SessionId", session_id() );
}

/**
 * Устанавливает текст поискового запроса
 * 
 * @param string $QueryText
 */
public function setQueryText($QueryText)
{
    $this->setData( "new_search_query", "Text", $QueryText );
}

/**
 * @return string - текст поискового запроса
 */
public function getQueryText()
{
    return $this->getData( "new_search_query", "Text" );
}


} // NewSearchQuery
