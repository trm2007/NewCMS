<?php

namespace NewCMS\Repositories;

use NewCMS\Domain\NewFeature;
use TRMEngine\DataMapper\TRMDataMapper;
use TRMEngine\DataSource\Interfaces\TRMDataSourceInterface;

class NewFeatureRepository extends NewIdTranslitRepository
{
static protected $DataObjectMap = array(
    "features" => array(
        TRMDataMapper::STATE_INDEX => TRMDataMapper::FULL_ACCESS_FIELD,
        TRMDataMapper::FIELDS_INDEX => array(
            "ID_Feature" => array(
                TRMDataMapper::KEY_INDEX => "PRI",
            ),
        ),
    ),
);


public function __construct(TRMDataSourceInterface $DataSource)
{
    parent::__construct(NewFeature::class, $DataSource);
}


} // NewUnitRepository

