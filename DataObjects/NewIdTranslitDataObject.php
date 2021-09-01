<?php

namespace NewCMS\DataObjects;

use NewCMS\Domain\Interfaces\NewTranslitInterface;
use TRMEngine\DataObject\TRMIdDataObject;
use TRMEngine\Helpers\TRMLib;

/**
 * абстрактный класс для объекта данных, которые могут быть сохранены в репозитории-хранилище,
 * у такого объекта может быть ID-идентификатор
 * в дополнение к TRMIDDataObject эти объекты обладают полем translit (или альяс русского наименования на английском),
 * например, для использования в URL
 *
 * @author TRM
 */
abstract class NewIdTranslitDataObject extends TRMIdDataObject implements NewTranslitInterface
{
/**
 * @var array - имя свойства с названием или заголовком объекта, обычно совпадает с полем name или title из таблицы БД
 */
//private $TitleFieldName = array("", "");
/**
 * @var array - имя свойства с транскрипцией названия на английском - используется для URL товара, группы или другого документа
 */
//private $TranslitFieldName = array("", "");


/**
 * @return array - TitleFieldName = array(объект, поле)
 */
static public function getTitleFieldName()
{
    return static::$TitleFieldName;
}
/**
 * @param array - TitleFieldName = array(объект, поле)
 */
static public function setTitleFieldName( array $TitleFieldName )
{
    static::$TitleFieldName[0] = reset($TitleFieldName);
    static::$TitleFieldName[1] = next($TitleFieldName);
}
/**
 * @return array - TranslitFieldName = array(объект, поле)
 */
static public function getTranslitFieldName()
{
    return static::$TranslitFieldName;
}
/**
 * @param array - TranslitFieldName = array(объект, поле)
 */
static public function setTranslitFieldName( array $TranslitFieldName )
{
    static::$TranslitFieldName[0] = reset($TranslitFieldName);
    static::$TranslitFieldName[1] = next($TranslitFieldName);
}
/**
 * устанавливает значение роидительским parent::setData($objectname, $fieldname, $value); 
 * если установленное поле является TitleFieldName,
 * то проверяет наличие данных в TranslitFieldName, если translit не установлен, 
 * то устанавливает автоматом, если данные уже есть, то не трогает,
 * таким образом можно вручную задавать любой translit, кроме пустого,
 * тогда он так же будет сформирован на основании значениея в $TitleFieldName
 * 
 * @param string $objectname
 * @param string $fieldname
 * @param mixed $value
 */
/*
public function setData($objectname, $fieldname, $value)
{
    // если устанавливается значение в поле, содержащее translit,
    if( $objectname == static::$TranslitFieldName[0]
        && $fieldname == static::$TranslitFieldName[1] )
    {
        // то проверем значение,
        // если новое значение пустое, 
        if( !$value || strlen($value) == 0 )
        {
            // и в объекте тоже не установлено, то заполняем автоматом
            $Translit = $this->getTranslit();
            if( !$Translit || strlen($Translit) == 0 )
            {
                 $this->translit();
            }
            // завершаем выполнение
            return;
        }
        
    }
    parent::setData($objectname, $fieldname, $value);
    // если это поле, содержащее наименование,
    if( $objectname == static::$TitleFieldName[0]
        && $fieldname == static::$TitleFieldName[1] )
    {
        // то проверем наличие translit,
        $Translit = $this->getTranslit();
        if( !$Translit || strlen($Translit) == 0 )
        {
            // если пустое, значить устанавливаем автоматом
            $this->translit();
        }
    }
}
*/


/**
 * получаем транслит из поля TranslitFieldName
 *
 * @return string|boolean
 */
public function getTranslit()
{
    return $this->getData( static::$TranslitFieldName[0], static::$TranslitFieldName[1] );
}

/**
 * задаем транслит, и сохраняем в поле TranslitFieldName
 *
 * @param string
 */
public function setTranslit($translit)
{
    parent::setData( static::$TranslitFieldName[0], static::$TranslitFieldName[1], $translit, true );
}

/**
 * формирует транслит на основе данных из поля TitleFieldName
 */
public function translit()
{
    $this->setTranslit( 
        TRMLib::translit( 
            $this->getData(static::$TitleFieldName[0], static::$TitleFieldName[1]), 
            true, 
            \GlobalConfig::$ConfigArray["Charset"] ) 
    );
}


} // TRMIDTranslitDataObject