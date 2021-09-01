<?php

namespace NewCMS\Repositories;

use NewCMS\Domain\NewMetaTag;
use TRMEngine\DataMapper\TRMDataMapper;
use TRMEngine\DataSource\Interfaces\TRMDataSourceInterface;

/**
 * Хранилище для работы с объектами MetaTag, 
 * в системе (в БД), как правило, хранится всего 3 таких объекта:
 * description, keywords, title
 */
class NewMetaTagRepository extends NewIdTranslitRepository
{
static protected $DataObjectMap = array(
    "new_metatags" => array(
        TRMDataMapper::STATE_INDEX => TRMDataMapper::FULL_ACCESS_FIELD,
        TRMDataMapper::FIELDS_INDEX => array(
            "ID_MetaTag" => array(
                TRMDataMapper::KEY_INDEX => "PRI",
            ),
            "Name" => array(),
            "Comment" => array(),
        ),
    ),
);


public function __construct(TRMDataSourceInterface $DataSource)
{
    parent::__construct(NewMetaTag::class, $DataSource);
}


} // NewMetaTagRepository

