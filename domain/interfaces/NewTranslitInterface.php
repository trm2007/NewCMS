<?php

namespace NewCMS\Domain\Interfaces;

/**
 * игтерфейс для объекта данных, которые обладают полем translit 
 * или альяс русского наименования (например, поля названия) на английском,
 * например, для использования в URL
 *
 * @author TRM
 */
interface NewTranslitInterface
{
/**
 * @return array - TitleFieldName = array( имя объекта, имя поле в объекте )
 */
static public function getTitleFieldName();
/**
 * @param array - TitleFieldName = array( имя объекта, имя поле в объекте )
 */
static public function setTitleFieldName( array $TitleFieldName );
/**
 * @return array - TranslitFieldName = array( имя объекта, имя поле в объекте )
 */
static public function getTranslitFieldName();
/**
 * @param array - TranslitFieldName = array( имя объекта, имя поле в объекте )
 */
static public function setTranslitFieldName( array $TranslitFieldName );

/**
 * получаем транслит из поля TranslitFieldName
 *
 * @return string|boolean
 */
function getTranslit();

/**
 * задаем транслит, и сохраняем в поле TranslitFieldName
 *
 * @param string
 */
function setTranslit($translit);

/**
 * формирует транслит на основе данных из поля TitleFieldName
 */
public function translit();


} // NewTranslitInterface