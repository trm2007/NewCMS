<?php

namespace NewCMS\Repositories;

use NewCMS\Domain\NewArticle;
use TRMEngine\DataMapper\TRMDataMapper;
use TRMEngine\DataSource\Interfaces\TRMDataSourceInterface;
use TRMEngine\Exceptions\TRMSqlQueryException;

class NewArticleRepository extends NewIdTranslitRepository
{
static protected $DataObjectMap = array(
    "articles" => array(
        TRMDataMapper::STATE_INDEX => TRMDataMapper::FULL_ACCESS_FIELD,
        TRMDataMapper::FIELDS_INDEX => array(
            "ID_article" => array(
                TRMDataMapper::KEY_INDEX => "PRI",
            ),
            "Reserv" => array(
                TRMDataMapper::RELATION_INDEX => array( TRMDataMapper::OBJECT_NAME_INDEX => "articlestype", 
                                                           TRMDataMapper::FIELD_NAME_INDEX => "ID_articlestype" ),
            )
        ),
    ),
    "articlestype" => array(
        TRMDataMapper::STATE_INDEX => TRMDataMapper::READ_ONLY_FIELD,
        TRMDataMapper::FIELDS_INDEX => array(
            "ID_articlestype" => array(
                TRMDataMapper::KEY_INDEX => "PRI",
            ),
        ),
        
    )
);

public function __construct(TRMDataSourceInterface $DataSource)
{
    parent::__construct(NewArticle::class, $DataSource);
}

/**
 * @param int $CurrentTypeId - ID-типа документа, количество статей которого нужно подсчитать в БД
 * 
 * @return int - возвращает общее количество документов типа 
 * как первый документ в текущей коллекции в БД,
 * если объект данных не установлен, или данные еще не получены, то вернется 0,
 * так же 0 вернется, если документов нет в БД
 *
 * @throws TRMSqlQueryException
 */
public function getCountOfArticlesOfCurrentType( $CurrentTypeId )
{
    $Query = "SELECT count(`ID_article`) FROM `articles` WHERE `Reserv` = {$CurrentTypeId}";
    $Res = $this->DataSource->getDBObject()->query($Query);
    if( !$Res )
    {
        throw new TRMSqlQueryException( "Не удалось получить документы данного типа: {$CurrentTypeId}!" );
    }
    return $Res->fetch_array(MYSQLI_NUM)[0];
}


public function getCountOfArticlesForUri( $Uri )
{
    $Query = "SELECT `ID_articlestype` FROM `articlestype` WHERE `ArticlesURL`='{$Uri}'";

    $Res = $this->DataSource->getDBObject()->query($Query);
    if( !$Res )
    {
        throw new TRMSqlQueryException( "Не найдены документы с таким URI: {$Uri}!" );
    }
    return $this->getCountOfArticlesOfCurrentType($Res->fetch_array(MYSQLI_NUM)[0]);
}


} // NewArticleRepository

