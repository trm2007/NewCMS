<?php

namespace NewCMS\Repositories;

use NewCMS\Domain\NewArticleGroup;
use TRMEngine\DataMapper\TRMDataMapper;
use TRMEngine\DataSource\Interfaces\TRMDataSourceInterface;

/**
 * с 2018.07.23 - основной класс для работы с хранилищем коллекции изображений
 *
 * @author TRM
 */
class NewArticleGroupRepository extends NewParentedRepository
{
static protected $DataObjectMap = array(
    "articlesgroups" => array( // rtn
        TRMDataMapper::STATE_INDEX => TRMDataMapper::FULL_ACCESS_FIELD,
        TRMDataMapper::FIELDS_INDEX => array(
            "ID_article" => array(
                TRMDataMapper::KEY_INDEX => "PRI",
            ),
            "ID_group" => array(
                TRMDataMapper::KEY_INDEX => "PRI",
                TRMDataMapper::RELATION_INDEX => array( 
                    TRMDataMapper::OBJECT_NAME_INDEX => "group", 
                    TRMDataMapper::FIELD_NAME_INDEX => "ID_group" ), 
            ),
        ),
    ),
    "group" => array(
        TRMDataMapper::STATE_INDEX => TRMDataMapper::READ_ONLY_FIELD,
        TRMDataMapper::FIELDS_INDEX => array(
            "ID_group" => array(
                TRMDataMapper::KEY_INDEX => "PRI",
            ),
        )
    ),
);


public function __construct(TRMDataSourceInterface $DataSource) 
{
    parent::__construct(NewArticleGroup::class, $DataSource);
    
    $this->DataMapper->setParentIdFieldName(array( "articlesgroups", "ID_article" ));
    $this->DataMapper->removeField("group", "GroupComment");
    $this->DataMapper->removeField("group", "GroupBigImage");
    // задает сортировку коллекции при выборке из БД по  стандартному набору
    $this->DataSource->setOrder( array( "GroupOrder" => "ASC" ) );
}


} // NewArticleGroupsCollectionRepository