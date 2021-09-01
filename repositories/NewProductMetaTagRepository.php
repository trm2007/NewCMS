<?php

namespace NewCMS\Repositories;

use NewCMS\Domain\NewProductMetaTag;
use TRMEngine\DataMapper\TRMDataMapper;
use TRMEngine\DataSource\Interfaces\TRMDataSourceInterface;

/**
 * класс для хранилища MetaTag-ов товара, 
 * зависит (принадлежит) товару - NewComplexProduct
 */
class NewProductMetaTagRepository extends NewParentedRepository
{
static protected $DataObjectMap = array(
    "new_products_metatags" => array( // rtn
        TRMDataMapper::STATE_INDEX => TRMDataMapper::FULL_ACCESS_FIELD,
        TRMDataMapper::FIELDS_INDEX => array(
            "ID_MetaTag" => array(
                TRMDataMapper::KEY_INDEX => "PRI",
                TRMDataMapper::RELATION_INDEX => array( 
                    TRMDataMapper::OBJECT_NAME_INDEX => "new_metatags", 
                    TRMDataMapper::FIELD_NAME_INDEX => "ID_MetaTag" ),
            ),
            "ID_Price" => array(
                TRMDataMapper::KEY_INDEX => "PRI",
            ),
            "Value" => array(),
        ),
    ),
    "new_metatags" => array(
        TRMDataMapper::STATE_INDEX => TRMDataMapper::READ_ONLY_FIELD,
        TRMDataMapper::FIELDS_INDEX => array(
            "ID_MetaTag" => array(
                TRMDataMapper::KEY_INDEX => "PRI",
            ),
            "Name" => array(),
            "Comment" => array(),
        )
    ),
);


public function __construct(TRMDataSourceInterface $DataSource)
{
    parent::__construct(NewProductMetaTag::class, $DataSource);
    $this->DataMapper->setParentIdFieldName(array( "new_products_metatags", "ID_Price" ));
    $this->DataSource->setOrder( array( "new_metatags.Name" => "ASC" ) );
}


} // NewProductMetaTagRepository