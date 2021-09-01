<?php

namespace NewCMS\Repositories;

use NewCMS\Domain\NewProductImage;
use TRMEngine\DataMapper\TRMDataMapper;
use TRMEngine\DataSource\Interfaces\TRMDataSourceInterface;

/**
 * с 2018.07.23 - основной класс для работы с хранилищем коллекции изображений
 *
 * @author TRM
 */
class NewProductImageRepository extends NewParentedRepository
{
static protected $DataObjectMap = array(
    "images" => array( // rtn
        TRMDataMapper::STATE_INDEX => TRMDataMapper::FULL_ACCESS_FIELD,
        TRMDataMapper::FIELDS_INDEX => array(
            "id_good" => array(
                TRMDataMapper::KEY_INDEX => "PRI",
            ),
            "id_image2" => array(
                TRMDataMapper::KEY_INDEX => "PRI",
            ),
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
static protected $ParentRelationIdFieldName = array( "images", "id_good" );


public function __construct(TRMDataSourceInterface $DataSource) 
{
    parent::__construct(NewProductImage::class, $DataSource);
    
    $this->DataMapper->setParentIdFieldName(array( "images", "id_good" ));
    // задает сортировку коллекции при выборке из БД по стандартному набору
    $this->DataSource->setOrder( array( "id_image2" => "DESC" ) );
}


} // NewImagesCollectionRepository