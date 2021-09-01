<?php

namespace NewCMS\Libs\Logger;

use NewCMS\Libs\Logger\NewLogger;
use NewCMS\Repositories\NewRepository;
use TRMEngine\DataMapper\TRMDataMapper;
use TRMEngine\DataSource\Interfaces\TRMDataSourceInterface;

class NewLoggerRepository extends NewRepository
{
static protected $DataObjectMap = array(
    "new_guest_info" => array(
        TRMDataMapper::STATE_INDEX => TRMDataMapper::FULL_ACCESS_FIELD,
        TRMDataMapper::FIELDS_INDEX => array(
            "id" => array(
                TRMDataMapper::KEY_INDEX => "PRI",
            ),
        ),
    ),
);


public function __construct(TRMDataSourceInterface $DataSource)
{
    parent::__construct(NewLogger::class, $DataSource);
}


} // NewLoggerRepository

