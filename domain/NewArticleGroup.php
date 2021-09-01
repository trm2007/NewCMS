<?php

namespace NewCMS\Domain;

use TRMEngine\DataObject\TRMParentedDataObject;

/**
 * с 2019.03.25 - класс для работы с коллекцией групп привязанных к статье
 */
class NewArticleGroup extends TRMParentedDataObject // NewSimpleProductCollection
{
/**
 * @var array - массив = (имя объекта, имя свойства) содержащего Id родителя в коллекции,
 * должен определяться в каждом дочернем классе со своими именами
 */
static protected $ParentIdFieldName = array( "articlesgroups", "ID_article" );


} // NewArticleGroupsCollection