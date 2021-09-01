<?php

namespace NewCMS\Repositories;

use TRMEngine\DataObject\Interfaces\TRMDataObjectInterface;
use TRMEngine\DataObject\Interfaces\TRMDataObjectsCollectionInterface;

/**
 * 2018.07.01 - объекты этого типа расширяют функционал update,
 * перед сохранением проверяют установлено ли у объекта данных поле с данными Транслита, 
 * если не установлено, пытается вызвать функцию translit
 *
 * @author TRM
 */
abstract class NewIdTranslitRepository extends NewRepository
{

/**
 * @param TRMDataObjectInterface $DataObject - объект, который будет добавлен в коллекцию сохраняемых
 */
public function update(TRMDataObjectInterface $DataObject )
{
    if( !strlen( $DataObject->getTranslit() ) )
    {
        $DataObject->translit();
    }
    return parent::update($DataObject);
}

public function updateCollection(TRMDataObjectsCollectionInterface $Collection)
{
    foreach( $Collection as $DataObject )
    {
        if( !strlen( $DataObject->getTranslit() ) )
        {
            $DataObject->translit();
        }
    }
    
    parent::updateCollection($Collection);
}


} // NewIdTranslitRepository