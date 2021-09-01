<?php

namespace NewCMS\Repositories;

use NewCMS\Domain\NewNews;
use TRMEngine\DataMapper\TRMDataMapper;
use TRMEngine\DataSource\Interfaces\TRMDataSourceInterface;

class NewNewsRepository extends NewRepository
{
static protected $DataObjectMap = array(
    "news" => array(
        "State" => TRMDataMapper::FULL_ACCESS_FIELD,
        "Fields" => array(
            "ID_new" => array(
                "Key" => "PRI",
            ),
        ),
    ),
);

public function __construct(TRMDataSourceInterface $DataSource)
{
    parent::__construct(NewNews::class, $DataSource);
    $this->setDefaultOrder();
}

public function setDefaultOrder()
{
    $this->DataSource->setOrderField("news.pubDate", false);
}

} // NewNewsRepository

