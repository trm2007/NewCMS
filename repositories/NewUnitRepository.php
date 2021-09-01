<?php

namespace NewCMS\Repositories;

use NewCMS\Domain\NewUnit;
use TRMEngine\DataMapper\TRMDataMapper;
use TRMEngine\DataSource\Interfaces\TRMDataSourceInterface;

class NewUnitRepository extends NewRepository
{
static protected $DataObjectMap = array(
    "unit" => array(
        "State" => TRMDataMapper::FULL_ACCESS_FIELD,
        "Fields" => array(
            "ID_unit" => array(
                "Key" => "PRI",
            ),
        ),
    ),
);

public function __construct(TRMDataSourceInterface $DataSource)
{
    parent::__construct(NewUnit::class, $DataSource);
}


} // NewUnitRepository

