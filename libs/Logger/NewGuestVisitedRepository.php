<?php

namespace NewCMS\Libs\Logger;

use NewCMS\Libs\Logger\NewGuestVisited;
use TRMEngine\DataMapper\TRMDataMapper;
use TRMEngine\DataMapper\TRMSafetyFields;
use TRMEngine\DataSource\Interfaces\TRMDataSourceInterface;
use TRMEngine\Repository\TRMRepository;

class NewGuestVisitedRepository extends TRMRepository // TRMIdDataObjectRepository
{
static protected $DataObjectMap = array(
    "new_guest_visited" => array(
        TRMDataMapper::STATE_INDEX => TRMDataMapper::FULL_ACCESS_FIELD,
        TRMDataMapper::FIELDS_INDEX => array(
            "session_id" => array(
                TRMDataMapper::STATE_INDEX => TRMDataMapper::FULL_ACCESS_FIELD
            ),
        ),
    ),
);

public function __construct(TRMDataSourceInterface $DataSource)
{
    parent::__construct(NewGuestVisited::class, $DataSource);

    $this->DataMapper = new TRMSafetyFields($DataSource->getDBObject());
    $this->DataMapper->setFieldsArray(static::$DataObjectMap);
    $this->DataMapper->completeSafetyFieldsFromDB();
}

/**
 * получает все просмотренные страницы для сессии
 * 
 * @param string $SessionId
 */
public function getVisitsForSession( $SessionId )
{
    $this->clearQueryParams();
    $this->DataSource->setGroupField("new_guest_visited.url");
    $this->DataSource->addWhereParam("new_guest_visited", "session_id", $SessionId);
    $Collection = $this->getAll();
    return $Collection;
}


} // NewGuestVisitedRepository

