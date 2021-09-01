<?php

namespace NewCMS\Repositories;

use NewCMS\Domain\NewProductFile;
use TRMEngine\DataMapper\TRMDataMapper;
use TRMEngine\DataSource\Interfaces\TRMDataSourceInterface;

/**
 * 2019.11.26 - основной класс для работы с хранилищем коллекции файлов для продуктов
 *
 * @author TRM
 */
class NewProductFileRepository extends NewParentedRepository
{
static protected $DataObjectMap = array(
    "new_products_files" => array(
        TRMDataMapper::STATE_INDEX => TRMDataMapper::FULL_ACCESS_FIELD,
        TRMDataMapper::FIELDS_INDEX => array(
            "ID_Product" => array(
                TRMDataMapper::KEY_INDEX => "PRI",
            ),
            "FileName" => array(),
            "Comment" => array(),
        ),
    ),
);
/**
 * @var array - массив array( имя объект, имя поля ) родительского ID в связующей таблице,
 * в данной реализации это одна из зависимостей, играющая роль главной, 
 * для которой выбираются все записи коллекции именно с одним таким ID,
 * например, для соотношения ( ID-товара-1 - [ID-товара-M, ID-характеристики-M] - ID-характеристики-1 )
 * такую роль играет ID-товара-M, для одного товара выбирается коллекция характеристик
 */
static protected $ParentRelationIdFieldName = array( "new_products_files", "ID_Product" );


public function __construct(TRMDataSourceInterface $DataSource) 
{
    parent::__construct(NewProductFile::class, $DataSource);
    
    $this->DataMapper->setParentIdFieldName(array( "new_products_files", "ID_Product" ));
    // задает сортировку коллекции при выборке из БД по стандартному набору
    $this->DataSource->setOrder( array( "FileName" => "DESC" ) );
}


} // NewProductFileRepository