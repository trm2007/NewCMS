<?php

namespace NewCMS\Domain;

use TRMEngine\DataArray\TRMDataArray;
use TRMEngine\DataObject\Interfaces\TRMDataObjectsCollectionInterface;
use TRMEngine\Exceptions\TRMException;

/**
 * клас для работы со списком из разных таблиц
 *
 * @author TRM
 */
class NewCommonList extends TRMDataArray
{
const ID_INDEX = "id";
const TITLE_INDEX = "title";

/**
 * @var array - имя поля для идентификатора объекта
 */
protected $IdFieldName = array();

/**
 * @var array - имя поля, содержащего название объекта
 */
protected $TitleFieldName = array();
 
/**
 * @return array - TitleFieldName объекта - array( имя объекта, имя поля )
 */
public function getIdFieldName()
{
    return $this->IdFieldName;
}

/**
 * @param array $IdFieldName - array( имя объекта, имя поля )
 */
public function setIdFieldName(array $IdFieldName)
{
    $this->IdFieldName[0] = reset($IdFieldName);
    $this->IdFieldName[1] = next($IdFieldName);
}

/**
 * @return array - TitleFieldName объекта - array( имя объекта, имя поля )
 */
public function getTitleFieldName()
{
    return $this->TitleFieldName;
}

/**
 * @param array $TitleFieldName - array( имя объекта, имя поля )
 */
public function setTitleFieldName(array $TitleFieldName)
{
    $this->TitleFieldName[0] = reset($TitleFieldName);
    $this->TitleFieldName[1] = next($TitleFieldName);
}

/**
 * инициализирует массив данных на основе объектов данных из коллекции
 * 
 * @param TRMDataObjectsCollectionInterface $Collection - коллекция с объектами данных
 */
public function initializeFromCollection( TRMDataObjectsCollectionInterface $Collection )
{
    if( empty($this->IdFieldName) )
    {
        throw new TRMException( __METHOD__ . "Не задан массив с именем Id-поля" );
    }
    if( empty($this->TitleFieldName) )
    {
        throw new TRMException( __METHOD__ . "Не задан массив с именем Title-поля" );
    }
    $this->clear();
    foreach( $Collection as $row )
    {
//        $this->addRow( array( 
//                static::ID_INDEX => $row[ $this->IdFieldName[0] ][ $this->IdFieldName[1] ], 
//                static::TITLE_INDEX => $row[ $this->TitleFieldName[0] ][ $this->TitleFieldName[1] ]
//            ) 
//        );
            $this->setRow(
                $row[ $this->IdFieldName[0] ][ $this->IdFieldName[1] ], 
                $row[ $this->TitleFieldName[0] ][ $this->TitleFieldName[1] ]
            );
    }
}


} // NewCommonList