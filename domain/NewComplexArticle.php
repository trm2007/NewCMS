<?php

namespace NewCMS\Domain;

use NewCMS\Domain\NewArticle;
use NewCMS\Domain\NewArticleGroup;
use TRMEngine\DataObject\Interfaces\TRMIdDataObjectInterface;
use TRMEngine\DataObject\TRMDataObjectsContainer;
use TRMEngine\DataObject\TRMParentedCollection;

/**
 * класс для работы с продуктом из таблицы table1 без вспомогательных объектов
 * 2018-06-30
 *
 * @author TRM
 */
class NewComplexArticle extends TRMDataObjectsContainer
{
/**
 * @var string - тип главного объекта 
 */
static protected $MainDataObjectType = NewArticle::class;

public function __construct()
{
    $this->MainDataObject = new NewArticle();
    $this->setChildCollection( "ArticleGroupsCollection", new NewArticleGroupsCollection($this) );
}

/**
 * 
 * @return NewArticle - возвращает простой объект статьи, 
 * являющийся главным для NewComplexArticle
 */
public function getArticle()
{
    return $this->MainDataObject;
}


} // NewComplexArticle


class NewArticleGroupsCollection extends TRMParentedCollection
{
/**
 * @var array - массив = (имя объекта, имя свойства) содержащего Id родителя в коллекции,
 * должен определяться в каждом дочернем классе со своими именами
 */
static protected $ParentIdFieldName;

public function __construct(TRMIdDataObjectInterface $ParentDataObject)
{
    parent::__construct(NewArticleGroup::class, $ParentDataObject);
}


} // NewArticleGroupsCollection
