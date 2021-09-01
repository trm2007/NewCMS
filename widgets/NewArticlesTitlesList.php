<?php

namespace NewCMS\Widgets;

use NewCMS\Domain\Exceptions\NewArticlesWrongTypeExceptions;
use NewCMS\Repositories\NewArticleRepository;

/**
 * формирует список типов документов-статей
 */
class NewArticlesTitlesList
{
/**
 * @var NewArticleRepository - репозиторий для статей
 */
//protected $Rep;

/**
 * @param \NewCMS\Widgets\NewArticleRepository $Rep - репозиторий для статей
 */
//public function __construct(NewArticleRepository $Rep)
//{
//    $this->Rep = $Rep;
//}

/**
 * выводит список названий статей в количестве $num, 
 * если $num == 0 выводим все, только определенного типа ArticlesType
 * 
 * @param NewArticleRepository $ArticlesRep
 * @param int $ArticlesType - тип статей, которые нужно вывести
 * @param int $num - количество выводимых заголовков
 * @param int $id_before - перед каким ID делать выборку, как правило выводятся более ранние статьи
 * @param boolean $AllArticles - TRUE = выводит все подряд, 
 * FALSE = только статьи для этого сайта и сателитов (сейчас не используется)
 * 
 * @return boolean
 */
static function printArticlesTitles(NewArticleRepository $ArticlesRep, $ArticlesType, $num=0, $id_before=0, $AllArticles = false)
{
    if( $ArticlesType === null )
    {
        throw new NewArticlesWrongTypeExceptions( " Не задан тип статей для списка! " );
    }

    $ArticlesRep->addCondition("articles", "Reserv", $ArticlesType);
            
    if($id_before) 
    {
        $ArticlesRep->addCondition("articles", "ID_article", $id_before, ">");
    }

    if(!$AllArticles)
    {
        $ArticlesRep->addCondition("articles", "onlyowner", "(0,1)", "IN");
    }
    $ArticlesRep->getDataSource()->setOrderField("ID_article", false);
    $ArticlesRep->getDataSource()->setOrderField("ArticleDate", false);

    if($num>0) 
    {
        $ArticlesRep->setLimit($num);
    }

    $ArticlesList = $ArticlesRep->getAll();

    if(!$ArticlesList ){ return false; }

    echo "<ol>\n";
    foreach( $ArticlesList as $Article )
    {
        printf("<li><a href=\"/%s/%s/%s\">%s</a> (%s)</li>", 
            \GlobalConfig::$ConfigArray["articlesListPrefix"], 
            $Article["articlestype"]["ArticlesURL"], 
            $Article["articles"]["ArticleURL"], 
            $Article["articles"]["Title"], 
            $Article["articles"]["ArticleDate"]);
    }
    echo "</ol>\n";

    return true;
}


} // NewArticlesTitlesList