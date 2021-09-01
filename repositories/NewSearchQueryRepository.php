<?php

namespace NewCMS\Repositories;

use NewCMS\Domain\NewSearchQuery;
use TRMEngine\DataMapper\TRMDataMapper;
use TRMEngine\DataSource\Interfaces\TRMDataSourceInterface;

class NewSearchQueryRepository extends NewRepository
{
static protected $DataObjectMap = array(
    "new_search_query" => array(
        "State" => TRMDataMapper::FULL_ACCESS_FIELD,
        "Fields" => array(
            "ID_SearchQuery" => array(
                "Key" => "PRI",
            ),
        ),
    ),
);


public function __construct(TRMDataSourceInterface $DataSource)
{
    parent::__construct(NewSearchQuery::class, $DataSource);
}


} // NewSearchQueryRepository

