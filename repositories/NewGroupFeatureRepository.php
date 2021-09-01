<?php

namespace NewCMS\Repositories;

use NewCMS\Domain\NewGroupFeature;
use TRMEngine\DataMapper\TRMDataMapper;
use TRMEngine\DataSource\Interfaces\TRMDataSourceInterface;

/**
 * класс для хранилища характеристик товара, зависит (принадлежит) товару - NewComplexProduct
 */
class NewGroupFeatureRepository extends NewParentedRepository
{
static protected $DataObjectMap = array(
    "groupfeature" => array( // rtn
        TRMDataMapper::STATE_INDEX => TRMDataMapper::FULL_ACCESS_FIELD,
        TRMDataMapper::FIELDS_INDEX => array(
            "ID_Feature" => array(
                TRMDataMapper::KEY_INDEX => "PRI",
                TRMDataMapper::RELATION_INDEX => array( 
                    TRMDataMapper::OBJECT_NAME_INDEX => "features", 
                    TRMDataMapper::FIELD_NAME_INDEX => "ID_Feature" 
                ), // из таблицы features будут выбраны все записи, 
                // значение поля `groupfeature`.`ID_Feature` которых содержится в списке `features`.`ID_Feature`
            ),
            "ID_Group" => array(
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
    parent::__construct(NewGroupFeature::class, $DataSource);
    $this->DataMapper->setParentIdFieldName(array( "groupfeature", "ID_Group" ));
    $this->DataSource->setOrder( array( "features.FeatureOrder" => "ASC" ) );
}


} // NewGroupfeaturesCollectionRepository