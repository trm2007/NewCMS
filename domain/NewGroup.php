<?php

namespace NewCMS\Domain;

use NewCMS\DataObjects\NewIdTranslitDataObject;
use TRMEngine\Helpers\TRMLib;

//******************************************************************************
// класс для объекта группа товаров , одно из свойств - ссылка на объект родителя, может быть ноль!!!
//******************************************************************************
class NewGroup extends NewIdTranslitDataObject
{
/**
 * @var array - имя свойства для идентификатора объекта, обычно совпадает с именем ID-поля из БД
 */
static protected $IdFieldName = array( "group", "ID_group" );
/**
 * @var array - имя свойства с названием или заголовком объекта, обычно совпадает с полем name или title из таблицы БД
 */
static protected $TitleFieldName = array( "group", "GroupTitle" );
/**
 * @var array - имя свойства с транскрипцией названия на английском - используется для URL товара, группы или другого документа
 */
static protected $TranslitFieldName = array( "group", "GroupTranslit" );
/**
 * @var NewGroup - экземпляр родительского объекта
 */
private $ParentGroupObject = null;
/**
 * @var string - полное наименование группы, включая родительские приставки, если это задано в БД
 */
private $GroupFullTitle = "";


/**
 * @return NewGroup - возвращает объект родительской группы
 */
public function getParentGroupObject()
{
    return $this->ParentGroupObject;
}

/**
 * @param NewGroup $ParentGroupObject - устанавливает объект родительской группы
 */
public function setParentGroupObject(NewGroup $ParentGroupObject)
{
    $this->ParentGroupObject = $ParentGroupObject;
}

/**
 * возвращает полное название группы , вместе с родительскими префиксами и постфиксами...
 * 
 * @return string - полное название группы
 */
public function getGroupFullTitle()
{
    return $this->GroupFullTitle;
}
/**
 * @return string - название группы без родительских приставок
 */
public function getGroupTitle()
{
    return $this->getData("group", "GroupTitle");
}
/**
 * @param string $GroupTitle - название группы без родительских приставок
 */
public function setGroupTitle($GroupTitle)
{
    $this->setData("group", "GroupTitle", $GroupTitle);
}

/**
 * формирует полное название группы вместе с родительскими добавками
 * 
 * @return string - возвращает сформированное название
 */
public function generateGroupFullTitle()
{
    if(!$this->ParentGroupObject || !$this->getData("group", "ParentGroupTitle") )
    {
        $this->GroupFullTitle = $this->getData("group", "GroupTitle");
    }
    else if($this->getData("group", "ParentGroupTitle")==1  && $this->ParentGroupObject!==null)
    {
        $this->ParentGroupObject->generateGroupFullTitle();
        $this->GroupFullTitle = $this->ParentGroupObject->GroupFullTitle." ".$this->getData("group", "GroupTitle");
    }
    else if($this->getData("group", "ParentGroupTitle") == 2 && $this->ParentGroupObject!==null)
    {
        $this->ParentGroupObject->generateGroupFullTitle();
        $this->GroupFullTitle = $this->getData("group", "GroupTitle") . " ".$this->ParentGroupObject->GroupFullTitle;
    }
    $this->GroupFullTitle = preg_replace("/\s+/", ' ', $this->GroupFullTitle); // удаляем повторяющиеся пробелы
    
    return $this->GroupFullTitle;
}

/**
 * генерирует транслит - URL-группы из названия,
 * учитывая все названия дочерних групп
 */
public function translit()
{
    if( empty($this->GroupFullTitle) )
    {
        $this->generateGroupFullTitle();
    }

    if( !empty($this->GroupFullTitle) )
    {
        $this->setData( 
                static::$TranslitFieldName[0], 
                static::$TranslitFieldName[1], 
                TRMLib::translit( $this->GroupFullTitle, true, \GlobalConfig::$ConfigArray["Charset"] ) 
            );
    }
    else
    {
        parent::translit();
    }
}


public function jsonSerialize()
{
    $Arr = parent::jsonSerialize();
    if( $this->ParentGroupObject ) { $Arr["Parent"] = $this->ParentGroupObject; }
    
    return $Arr;
}


} // NewGroup