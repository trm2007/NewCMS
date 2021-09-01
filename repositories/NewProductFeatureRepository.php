<?php

namespace NewCMS\Repositories;

use NewCMS\Domain\NewProductFeature;
use TRMEngine\DataMapper\TRMDataMapper;
use TRMEngine\DataSource\Interfaces\TRMDataSourceInterface;

/**
 * класс для хранилища характеристик товара, зависит (принадлежит) товару - NewComplexProduct
 */
class NewProductFeatureRepository extends NewParentedRepository
{
static protected $DataObjectMap = array(
    "goodsfeatures" => array( // rtn
        TRMDataMapper::STATE_INDEX => TRMDataMapper::FULL_ACCESS_FIELD,
        TRMDataMapper::FIELDS_INDEX => array(
            "ID_Feature" => array(
                TRMDataMapper::KEY_INDEX => "PRI",
                TRMDataMapper::RELATION_INDEX => array( 
                    TRMDataMapper::OBJECT_NAME_INDEX => "features", 
                    TRMDataMapper::FIELD_NAME_INDEX => "ID_Feature" ), // из таблицы table1 будут выбраны все записи, значение поля `table1`.`ID_price` которых содержится в списке `complect`.`ID_Price`
            ),
            "ID_Price" => array(
                TRMDataMapper::KEY_INDEX => "PRI",
            ),
        ),
    ),
    "features" => array( // tn
        TRMDataMapper::STATE_INDEX => TRMDataMapper::READ_ONLY_FIELD,
        TRMDataMapper::FIELDS_INDEX => array(
            "ID_Feature" => array(
                TRMDataMapper::KEY_INDEX => "PRI",
            ),
        )
    ),
);


public function __construct(TRMDataSourceInterface $DataSource)
{
    parent::__construct(NewProductFeature::class, $DataSource);
    $this->DataMapper->setParentIdFieldName(array( "goodsfeatures", "ID_Price" ));
    $this->DataSource->setOrder( array( "features.FeatureOrder" => "ASC" ) );
}


} // NewProductFeaturesCollectionRepository